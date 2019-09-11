<?php
namespace CTIC\Grh\Report\Infrastructure\Controller;

use Cocur\Slugify\Slugify;
use CTIC\App\Base\Domain\EntityActions;
use CTIC\App\Base\Infrastructure\Controller\ResourceController;
use CTIC\App\Base\Infrastructure\View\Rest\View;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Employee\Domain\EmployeeInterface;
use CTIC\Grh\Employee\Domain\EmployeeLow;
use CTIC\Grh\Employee\Domain\EmployeePersonalAffairs;
use CTIC\Grh\Employee\Infrastructure\Repository\EmployeeLowRepository;
use CTIC\Grh\Employee\Infrastructure\Repository\EmployeePersonalAffairsRepository;
use CTIC\Grh\Event\Domain\Event;
use CTIC\Grh\Event\Domain\EventEmployee;
use CTIC\Grh\Event\Infrastructure\Helper\EventHelper;
use CTIC\Grh\Fichar\Domain\Fichar;
use CTIC\Grh\Fichar\Infrastructure\Repository\FicharRepository;
use CTIC\Grh\Report\Domain\ReportFilter;
use CTIC\Grh\Report\Infrastructure\Form\Type\ReportFilterType;
use CTIC\Grh\Report\Infrastructure\Repository\ReportRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class ReportController extends ResourceController
{

    /**
     * @var ReportRepository
     */
    protected $repository;

    /**
     * @param $lastMonthEvents
     * @return array
     */
    protected function getHoursPerEmployeeByMonth($lastMonthEvents)
    {
        $reportHoursPerEmployeeByMonth = array();
        /** @var Event $lastMonthEvent */
        foreach ($lastMonthEvents as $lastMonthEvent) {
            $eventEmployees = $lastMonthEvent->getEventEmployee();

            /** @var EventEmployee $eventEmployee */
            foreach ($eventEmployees as $eventEmployee) {
                $employee = $eventEmployee->getEmployee();
                $turn = $eventEmployee->getTurn();
                if ($turn == EventEmployee::TURN_ALL) {
                    $intervalDates = $lastMonthEvent->getFromDate()->diff($lastMonthEvent->getToDate());
                } elseif ($turn == EventEmployee::TURN_CONCERT) {
                    $intervalDates = $lastMonthEvent->getFromDate()->diff($lastMonthEvent->getShiftChangeDate());
                } else {
                    $intervalDates = $lastMonthEvent->getShiftChangeDate()->diff($lastMonthEvent->getToDate());
                }
                $eventHours = (float) (($intervalDates->d * 24) + $intervalDates->h + ($intervalDates->i / 60));
                if ($lastMonthEvent->getToDate()->format('d') == $lastMonthEvent->getToDate()->format('t')) {
                    //$eventHours = $eventHours + 1;
                }
                $employeeId = $eventEmployee->getEmployee()->getId();
                if (empty($reportHoursPerEmployeeByMonth[$employeeId])) {
                    $reportHoursPerEmployeeByMonth[$employeeId] = new \stdClass();
                    $reportHoursPerEmployeeByMonth[$employeeId]->employee = $employee->getName() . ' ' . $employee->getSurname();
                    $reportHoursPerEmployeeByMonth[$employeeId]->hours = $eventHours;
                } else {
                    $reportHoursPerEmployeeByMonth[$employeeId]->hours = $reportHoursPerEmployeeByMonth[$employeeId]->hours + $eventHours;
                }
            }
        }

        return $reportHoursPerEmployeeByMonth;
    }

    /**
     * @param $lastMonthEvents
     * @param $contractType
     * @return array
     */
    protected function getEventDatesPerEmployee($lastMonthEvents, $contractType)
    {
        $reportEventDatesPerEmployee = array();
        /** @var Event $lastMonthEvent */
        foreach ($lastMonthEvents as $lastMonthEvent) {
            $eventEmployees = $lastMonthEvent->getEventEmployee();

            /** @var EventEmployee $eventEmployee */
            foreach ($eventEmployees as $eventEmployee) {
                $employee = $eventEmployee->getEmployee();

                if ($employee->getContractType() !== $contractType && $contractType !== 'all') {
                    continue;
                }

                $employeeId = $eventEmployee->getEmployee()->getId();
                $turn = $eventEmployee->getTurn();
                $dates = 'Desde ';
                $diff = null;
                if ($turn == EventEmployee::TURN_ALL) {
                    $diff = $lastMonthEvent->getFromDate()->diff($lastMonthEvent->getToDate());
                    $dates = $dates . $lastMonthEvent->getFromDate()->format('d-m-Y H:i:s') . ' hasta ' .
                        $lastMonthEvent->getToDate()->format('d-m-Y H:i:s');
                } elseif ($turn == EventEmployee::TURN_CONCERT) {
                    $diff = $lastMonthEvent->getFromDate()->diff($lastMonthEvent->getShiftChangeDate());
                    $dates = $dates . $lastMonthEvent->getFromDate()->format('d-m-Y H:i:s') . ' hasta ' .
                        $lastMonthEvent->getShiftChangeDate()->format('d-m-Y H:i:s');
                } else {
                    $diff = $lastMonthEvent->getShiftChangeDate()->diff($lastMonthEvent->getToDate());
                    $dates = $dates . $lastMonthEvent->getShiftChangeDate()->format('d-m-Y H:i:s') . ' hasta ' .
                        $lastMonthEvent->getToDate()->format('d-m-Y H:i:s');
                }
                $hoursDiff = ' (?)';
                if ($diff) {
                    $hoursDiff = ' (' . $diff->format('%h') . ':' . $diff->format('%i') . ')';
                }
                $dates = $dates . $hoursDiff . '.';
                if (empty($reportEventDatesPerEmployee[$employeeId])) {
                    $reportEventDatesPerEmployee[$employeeId] = new \stdClass();
                    $reportEventDatesPerEmployee[$employeeId]->employee = $employee->getName() . ' ' . $employee->getSurname();
                    $reportEventDatesPerEmployee[$employeeId]->events = array();
                    $reportEventDatesPerEmployee[$employeeId]->events[] = $dates;
                } else {
                    $reportEventDatesPerEmployee[$employeeId]->events[] = $dates;
                }
            }
        }

        return $reportEventDatesPerEmployee;
    }

    /**
     * @param $lastMonthEvents
     * @param $contractType
     * @return array
     */
    protected function getReportEventDatesPerEmployee($lastMonthEvents, $contractType)
    {
        $reportEventDatesPerEmployee = $this->getEventDatesPerEmployee($lastMonthEvents, $contractType);

        $reportEventDatesPerEmployeeOld = $reportEventDatesPerEmployee;
        $reportEventDatesPerEmployee = array();
        foreach ($reportEventDatesPerEmployeeOld as $value) {
            $reportEventDatesPerEmployee[] = $value;
        }

        return $reportEventDatesPerEmployee;
    }

    /**
     * @param $lastMonthEvents
     * @return array
     */
    protected function getReportHoursPerEmployeeByMonth($lastMonthEvents)
    {
        $reportHoursPerEmployeeByMonth = $this->getHoursPerEmployeeByMonth($lastMonthEvents);

        $reportHoursPerEmployeeByMonthOld = $reportHoursPerEmployeeByMonth;
        $reportHoursPerEmployeeByMonth = array();
        foreach ($reportHoursPerEmployeeByMonthOld as $value) {
            $reportHoursPerEmployeeByMonth[] = $value;
        }

        return $reportHoursPerEmployeeByMonth;
    }

    /**
     * @param $lastMonthEvents
     * @return array
     */
    protected function getReportCostPerEmployeeByEvent($lastMonthEvents)
    {
        $reportCostPerEmployeeByEvent = array();
        /** @var Event $lastMonthEvent */
        foreach ($lastMonthEvents as $lastMonthEvent) {
            $eventId = $lastMonthEvent->getId();
            $eventEmployees = $lastMonthEvent->getEventEmployee();

            /** @var EventEmployee $eventEmployee */
            foreach ($eventEmployees as $eventEmployee) {
                $employee = $eventEmployee->getEmployee();
                $turn = $eventEmployee->getTurn();
                if ($turn == EventEmployee::TURN_ALL) {
                    $intervalDates = $lastMonthEvent->getFromDate()->diff($lastMonthEvent->getToDate());
                } elseif ($turn == EventEmployee::TURN_CONCERT) {
                    $intervalDates = $lastMonthEvent->getFromDate()->diff($lastMonthEvent->getShiftChangeDate());
                } else {
                    $intervalDates = $lastMonthEvent->getShiftChangeDate()->diff($lastMonthEvent->getToDate());
                }
                $eventHours = (float) (($intervalDates->d * 24) + $intervalDates->h + ($intervalDates->i / 60));
                $costType = $employee->getPricePerHourType();
                $costConcert = 0;
                $costDisco = 0;
                if ($costType == Employee::PRICE_PER_HOUR_FIX) {
                    $cost = $employee->getCostPerHour();
                    $costDisco = $employee->getCostPerHour();
                } else {
                    if ($turn == EventEmployee::TURN_ALL) {
                        $intervalDatesConcert = $lastMonthEvent->getFromDate()->diff($lastMonthEvent->getShiftChangeDate());
                        $concertHours = (float) (($intervalDatesConcert->d * 24) + $intervalDatesConcert->h + ($intervalDatesConcert->i / 60));
                        $costConcert = $concertHours * $employee->getCostPerHour();
                        $intervalDatesDisco = $lastMonthEvent->getShiftChangeDate()->diff($lastMonthEvent->getToDate());
                        $discoHours = (float) (($intervalDatesDisco->d * 24) + $intervalDatesDisco->h + ($intervalDatesDisco->i / 60));
                        $costDisco = $discoHours * $employee->getCostPerHour();
                    } elseif ($turn == EventEmployee::TURN_CONCERT) {
                        $costConcert = $eventHours * $employee->getCostPerHour();
                    } else {
                        $costDisco = $eventHours * $employee->getCostPerHour();
                    }
                    $cost = $eventHours * $employee->getCostPerHour();
                }
                if (empty($reportCostPerEmployeeByEvent[$eventId])) {
                    $reportCostPerEmployeeByEvent[$eventId] = new \stdClass();
                    $reportCostPerEmployeeByEvent[$eventId]->date = $lastMonthEvent->getFromDate()->format('Y-m-d H:i:s');
                    $reportCostPerEmployeeByEvent[$eventId]->event = $lastMonthEvent->getName();
                    $resumeCategories = EventHelper::getResumeEmployeesByCategoryAction($lastMonthEvent);
                    $reportCostPerEmployeeByEvent[$eventId]->categories = '';
                    foreach ($resumeCategories as $resumeCategory) {
                        $reportCostPerEmployeeByEvent[$eventId]->categories = $reportCostPerEmployeeByEvent[$eventId]->categories .
                            $resumeCategory->name . ': ' . $resumeCategory->employees . "<br>";
                    }
                    $reportCostPerEmployeeByEvent[$eventId]->costConcert = $costConcert;
                    $reportCostPerEmployeeByEvent[$eventId]->costDisco = $costDisco;
                    $reportCostPerEmployeeByEvent[$eventId]->cost = $cost;
                } else {
                    $reportCostPerEmployeeByEvent[$eventId]->costConcert = $reportCostPerEmployeeByEvent[$eventId]->costConcert + $costConcert;
                    $reportCostPerEmployeeByEvent[$eventId]->costDisco = $reportCostPerEmployeeByEvent[$eventId]->costDisco + $costDisco;
                    $reportCostPerEmployeeByEvent[$eventId]->cost = $reportCostPerEmployeeByEvent[$eventId]->cost + $cost;
                }
            }
        }
        $reportCostPerEmployeeByEventOld = $reportCostPerEmployeeByEvent;
        $reportCostPerEmployeeByEvent = array();
        foreach ($reportCostPerEmployeeByEventOld as $value) {
            $reportCostPerEmployeeByEvent[] = $value;
        }

        return $reportCostPerEmployeeByEvent;
    }

    /**
     * @param $lastEmployeeLows
     * @return array
     */
    protected function getEmployeesFromLastEmployeeLow($lastEmployeeLows)
    {
        $slugify = new Slugify();
        $employees = array();
        /** @var EmployeeLow $lastEmployeeLow */
        foreach ($lastEmployeeLows as $lastEmployeeLow) {
            $employee = $lastEmployeeLow->getEmployee();
            $employeeId = $employee->getId();
            $employeeNameSlugged = $slugify->slugify($employee->getName());
            $employeeSurameSlugged = $slugify->slugify($employee->getSurname());

            $employees[$employeeId] = $employeeNameSlugged . '-' . $employeeSurameSlugged;
        }
        $employeesOld = $employees;
        $employees = array();
        foreach ($employeesOld as $value) {
            $employees[] = $value;
        }

        return $employees;
    }

    /**
     * @param $lastEmployeeLows
     * @return array
     */
    protected function getReportEmployeeLowByDate($lastEmployeeLows)
    {
        $slugify = new Slugify();
        $reportEmployeeLowByDate = array();
        /** @var EmployeeLow $lastEmployeeLow */
        foreach ($lastEmployeeLows as $lastEmployeeLow) {
            $employeeLowId = $lastEmployeeLow->getId();
            $employee = $lastEmployeeLow->getEmployee();
            $employeeSurnameSlugged = $slugify->slugify($employee->getSurname());
            $employeeNameSlugged = $slugify->slugify($employee->getName()) . '-' . $employeeSurnameSlugged;
            $dateFrom = $lastEmployeeLow->getFromDate()->format('Y-m-d');
            $dateTo = $lastEmployeeLow->getToDate()->format('Y-m-d');
            $nameEmpleado = 'empleado-' . $employeeNameSlugged;
            $surnameEmpleado = 'empleado-' . $employeeNameSlugged . '-surname';
            $nameEmpleadoId = 'empleado-' . $employeeNameSlugged . '-id';
            $nameEmpleadoDesdeFecha = $employeeNameSlugged . '-desde-fecha';
            $nameEmpleadoHastaFecha = $employeeNameSlugged . '-hasta-fecha';

            if (!isset($reportEmployeeLowByDate[$dateFrom])) {
                $reportEmployeeLowByDate[$dateFrom] = new \stdClass();
            }
            $reportEmployeeLowByDate[$dateFrom]->$nameEmpleado = $employeeNameSlugged;
            $reportEmployeeLowByDate[$dateFrom]->$surnameEmpleado = $employeeSurnameSlugged;
            $reportEmployeeLowByDate[$dateFrom]->$nameEmpleadoId = $employee->getId();
            $reportEmployeeLowByDate[$dateFrom]->$nameEmpleadoDesdeFecha = $lastEmployeeLow->getFromDate()->format('Y-m-d');
            $reportEmployeeLowByDate[$dateFrom]->date = $dateFrom;

            if (!isset($reportEmployeeLowByDate[$dateTo])) {
                $reportEmployeeLowByDate[$dateTo] = new \stdClass();
            }
            $reportEmployeeLowByDate[$dateTo]->$nameEmpleado = $employeeNameSlugged;
            $reportEmployeeLowByDate[$dateTo]->$surnameEmpleado = $employeeSurnameSlugged;
            $reportEmployeeLowByDate[$dateTo]->$nameEmpleadoId = $employee->getId();
            $reportEmployeeLowByDate[$dateTo]->$nameEmpleadoHastaFecha = $lastEmployeeLow->getToDate()->format('Y-m-d');
            $reportEmployeeLowByDate[$dateTo]->date = $dateTo;
        }
        ksort($reportEmployeeLowByDate);
        $reportEmployeeLowByDateOld = $reportEmployeeLowByDate;
        $reportEmployeeLowByDate = array();
        foreach ($reportEmployeeLowByDateOld as $value) {
            $reportEmployeeLowByDate[] = $value;
        }

        return $reportEmployeeLowByDate;
    }

    /**
     * @param $lastFichar
     * @return array
     */
    protected function getFicharFromLast($lastFichar)
    {
        $reportFicharByLast = array();
        $ficharStakeByEmployeeId = array();
        /** @var Fichar $fichar */
        foreach ($lastFichar as $fichar) {
            $employee = $fichar->getEmployee();
            if ($fichar->getAction() == Fichar::IN) {
                $ficharStakeByEmployeeId[$employee->getId()] = $fichar;
                continue;
            } else {
                if (empty($ficharStakeByEmployeeId[$employee->getId()])) {
                    continue;
                }
                /** @var Fichar $ficharIn */
                $ficharIn = $ficharStakeByEmployeeId[$employee->getId()];
                $ficharStakeByEmployeeId[$employee->getId()] = null;
            }
            $intervalDates = $ficharIn->getDate()->diff($fichar->getDate());
            $eventHours = number_format((float) (($intervalDates->d * 24) + $intervalDates->h + ($intervalDates->i / 60)), 2, ',', '');

            if ($eventHours > 15) {
                continue;
            }

            $employeeId = $employee->getId();
            if (empty($reportFicharByLast[$employeeId])) {
                $reportFicharByLast[$employeeId] = new \stdClass();
                $reportFicharByLast[$employeeId]->employee = $employee->getName() . ' ' . $employee->getSurname();
                $reportFicharByLast[$employeeId]->hours = $eventHours;
            } else {
                $reportFicharByLast[$employeeId]->hours = $reportFicharByLast[$employeeId]->hours + $eventHours;
            }
        }
        $reportFicharByLastOld = $reportFicharByLast;
        $reportFicharByLast = array();
        foreach ($reportFicharByLastOld as $value) {
            $reportFicharByLast[] = $value;
        }

        return $reportFicharByLast;
    }

    /**
     * @param $lastFichar
     * @param $contractType
     * @return array
     */
    protected function getFicharFromLastAndDayList($lastFichar, $contractType)
    {
        $reportFicharByLast = array();
        $ficharStakeByEmployeeId = array();
        /** @var Fichar $fichar */
        foreach ($lastFichar as $fichar) {
            $employee = $fichar->getEmployee();

            if ($employee->getContractType() !== $contractType && $contractType !== 'all') {
                continue;
            }

            if ($fichar->getAction() == Fichar::IN) {
                $ficharStakeByEmployeeId[$employee->getId()] = $fichar;
                continue;
            } else {
                if (empty($ficharStakeByEmployeeId[$employee->getId()])) {
                    continue;
                }
                /** @var Fichar $ficharIn */
                $ficharIn = $ficharStakeByEmployeeId[$employee->getId()];
                $ficharStakeByEmployeeId[$employee->getId()] = null;
            }
            $intervalDates = $ficharIn->getDate()->diff($fichar->getDate());
            $eventHours = (float) (($intervalDates->d * 24) + $intervalDates->h + ($intervalDates->i / 60));

            if ($eventHours > 15) {
                continue;
            }

            $employeeId = $employee->getId();


            $dates = 'Desde ';
            $dates = $dates . $ficharIn->getDate()->format('d-m-Y H:i:s') . ' hasta ' .
                $fichar->getDate()->format('d-m-Y H:i:s');
            $hoursDiff = ' (?)';
            if ($eventHours) {
                $hoursDiff = ' (' . number_format($eventHours, 2, ',', '') . ')';
            }
            $dates = $dates . $hoursDiff . '.';
            if (empty($reportFicharByLast[$employeeId])) {
                $reportFicharByLast[$employeeId] = new \stdClass();
                $reportFicharByLast[$employeeId]->employee = $employee->getName() . ' ' . $employee->getSurname();
                $reportFicharByLast[$employeeId]->events = array();
                $reportFicharByLast[$employeeId]->events[] = $dates;
            } else {
                $reportFicharByLast[$employeeId]->events[] = $dates;
            }
        }

        return $reportFicharByLast;
    }

    /**
     * @param $lastMonthEvents
     * @param $contractType
     * @return array
     */
    protected function getReportFicharFromLastAndDayList($lastMonthEvents, $contractType)
    {
        $reportFicharByLast = $this->getFicharFromLastAndDayList($lastMonthEvents, $contractType);

        $reportFicharByLastOld = $reportFicharByLast;
        $reportFicharByLastToReturn = array();
        foreach ($reportFicharByLastOld as $value) {
            $reportFicharByLastToReturn[] = $value;
        }

        return $reportFicharByLastToReturn;
    }

    /**
     * @param ReportFilter $reportFilter
     *
     * @return void
     *
     * @throws
     */
    protected function getDefaultDates(ReportFilter $reportFilter): void
    {
        $month = date('n');
        $year = date('Y');
        if ($month == '1') {
            $month = '12';
            $year = $year - 1;
        } else {
            $month = $month - 1;
        }
        if (strlen($month) < 2) {
            $month = '0' . $month;
        }
        $reportFilter->fromDate = new \DateTime($year . '-' . $month . '-' . '01');
        $reportFilter->toDate = new \DateTime($reportFilter->fromDate->format('Y-m-d'));
    }

    /**
     * @param $lastMonthEvents
     *
     * @return void
     *
     * @throws
     */
    protected function sendEmailsToEmployees($lastMonthEvents): void
    {
        $eventDatesPerEmployeeByMonth = $this->getEventDatesPerEmployee($lastMonthEvents, 'all');
        $employeeRepository = $this->manager->getRepository(Employee::class);

        $counter = 0;
        foreach ($eventDatesPerEmployeeByMonth as $employeeId => $eventsEmployee) {
            /** @var Employee $employee */
            $employee = $employeeRepository->find($employeeId);
            if (empty($employee)) {
                continue;
            }

            try {
                $configYaml = Yaml::parse(file_get_contents(__DIR__.'/../../../../../../config.yml'));
                $smtpUser = $configYaml['smtp']['user'];
                $smtpPassword = $configYaml['smtp']['password'];
                $smtpHost = $configYaml['smtp']['host'];

                $mailer = new PHPMailer(true);
                $mailer->isSMTP();                                      // Set mailer to use SMTP
                $mailer->Host = $smtpHost;  // Specify main and backup SMTP servers
                $mailer->SMTPAuth = true;                               // Enable SMTP authentication
                $mailer->Username = $smtpUser;                 // SMTP username
                $mailer->Password = $smtpPassword;                           // SMTP password
                $mailer->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
                $mailer->Port = 465;

                $mailer->setFrom($smtpUser, 'Gestor de empleados');
                $mailer->addAddress($employee->getEmail(), $employee->getName() . ' ' . $employee->getSurname());

                $mailer->isHTML(true);                                  // Set email format to HTML
                $mailer->Subject = 'Fechas programados';
                $mailer->Body    = 'Hola ' . $eventsEmployee->employee . ",<br><br>" . 'Aquí tienes el listado de días que tienes programado:' . "<br>" . join("<br>", $eventsEmployee->events) . "<br><br>Muchas gracias.";
                $mailer->AltBody = 'Tu gestor de correos no acepta HTML';

                $mailer->send();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            $counter++;
            if ($counter == 4) {
                $counter = 0;
                sleep(3);
            }
        }
    }

    /**
     * @param $lastFichar
     *
     * @return void
     *
     * @throws
     */
    protected function sendFicharEmailsToEmployees($lastFichar): void
    {
        $eventDatesPerEmployeeByMonth = $this->getFicharFromLastAndDayList($lastFichar, 'all');
        $employeeRepository = $this->manager->getRepository(Employee::class);

        $counter = 0;
        foreach ($eventDatesPerEmployeeByMonth as $employeeId => $eventsEmployee) {
            /** @var Employee $employee */
            $employee = $employeeRepository->find($employeeId);
            if (empty($employee)) {
                continue;
            }

            try {
                $configYaml = Yaml::parse(file_get_contents(__DIR__.'/../../../../../../config.yml'));
                $smtpUser = $configYaml['smtp']['user'];
                $smtpPassword = $configYaml['smtp']['password'];
                $smtpHost = $configYaml['smtp']['host'];

                $mailer = new PHPMailer(true);
                $mailer->isSMTP();                                      // Set mailer to use SMTP
                $mailer->Host = $smtpHost;  // Specify main and backup SMTP servers
                $mailer->SMTPAuth = true;                               // Enable SMTP authentication
                $mailer->Username = $smtpUser;                 // SMTP username
                $mailer->Password = $smtpPassword;                           // SMTP password
                $mailer->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
                $mailer->Port = 465;

                $mailer->setFrom($smtpUser, 'Gestor de empleados');
                $mailer->addAddress($employee->getEmail(), $employee->getName() . ' ' . $employee->getSurname());

                $mailer->isHTML(true);                                  // Set email format to HTML
                $mailer->Subject = 'Fechas del control de presencia';
                $mailer->Body    = 'Hola ' . $eventsEmployee->employee . ",<br><br>" . 'Aquí tienes el listado de días que has trabajado:' . "<br>" . join("<br>", $eventsEmployee->events) . "<br><br>Muchas gracias.";
                $mailer->AltBody = 'Tu gestor de correos no acepta HTML';

                $mailer->send();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            $counter++;
            if ($counter == 4) {
                $counter = 0;
                sleep(3);
            }
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws
     */
    public function indexAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $action = 'report';

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }
        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);
        $reportFilter = new ReportFilter();

        $isPost = false;
        if (!$request->isMethod('POST')) {
            $this->getDefaultDates($reportFilter);

            $fromDate = $request->cookies->get('fromDate');
            if (!empty($fromDate)) {
                $reportFilter->fromDate = new \DateTime($fromDate);
            }
            $toDate = $request->cookies->get('toDate');
            if (!empty($toDate)) {
                $reportFilter->toDate = new \DateTime($toDate);
            }
        } else {
            $isPost = true;
        }

        $form = $this->resourceFormFactory->create($configuration, $reportFilter, ReportFilterType::class);

        if ($isPost == true && !$form->handleRequest()->isValid()) {
            $this->getDefaultDates($reportFilter);
        }

        $lastMonthEvents = $this->repository->findTotalHoursByEmployee($reportFilter);

        /** @var EmployeeLowRepository $employeeLowRepository */
        $employeeLowRepository = $this->manager->getRepository(EmployeeLow::class);
        $lastEmployeeLows = $employeeLowRepository->findByRangeDate($reportFilter);
        $lastEmployeeIdLow = $employeeLowRepository->findLastEmployeeIdByRangeDate($reportFilter);

        /** @var EmployeePersonalAffairsRepository $employeePersonalAffairsRepository */
        $employeePersonalAffairsRepository = $this->manager->getRepository(EmployeePersonalAffairs::class);
        $lastEmployeePersonalAffairs = $employeePersonalAffairsRepository->findByRangeDate($reportFilter);
        $lastEmployeeIdPersonalAffairs = $employeePersonalAffairsRepository->findLastEmployeeIdByRangeDate($reportFilter);

        $lastAbseceEmployeeId = ($lastEmployeeIdPersonalAffairs > $lastEmployeeIdLow)? $lastEmployeeIdPersonalAffairs : $lastEmployeeIdLow;

        /** @var FicharRepository $ficharRepository */
        $ficharRepository = $this->manager->getRepository(Fichar::class);
        $lastFichar = $ficharRepository->findByRangeDate($reportFilter);

        // Reports
        $reportEventDatesPerEmployeeInt = $this->getReportEventDatesPerEmployee($lastMonthEvents, EmployeeInterface::CONTRACT_TYPE_INTERN);
        $reportEventDatesPerEmployeeSub = $this->getReportEventDatesPerEmployee($lastMonthEvents, EmployeeInterface::CONTRACT_TYPE_SUB);
        $reportHoursPerEmployeeByMonth = $this->getReportHoursPerEmployeeByMonth($lastMonthEvents);
        $reportCostPerEmployeeByEvent = $this->getReportCostPerEmployeeByEvent($lastMonthEvents);
        $reportEmployeeLowByDate = $this->getReportEmployeeLowByDate($lastEmployeeLows);
        $reportEmployeeLowByDateEmployees = $this->getEmployeesFromLastEmployeeLow($lastEmployeeLows);
        $reportEmployeePersonalAffairsByDate = $this->getReportEmployeeLowByDate($lastEmployeePersonalAffairs);
        $reportEmployeePersonalAffairsByDateEmployees = $this->getEmployeesFromLastEmployeeLow($lastEmployeePersonalAffairs);
        $reportFicharByDate = $this->getFicharFromLast($lastFichar);
        $reportFicharByDateAndDayListInt = $this->getFicharFromLastAndDayList($lastFichar, EmployeeInterface::CONTRACT_TYPE_INTERN);
        $reportFicharByDateAndDayListSub = $this->getFicharFromLastAndDayList($lastFichar, EmployeeInterface::CONTRACT_TYPE_SUB);
        $reportAbsence = array_merge($reportEmployeeLowByDate, $reportEmployeePersonalAffairsByDate);
        usort($reportAbsence, function ($a, $b) {
            return new \DateTime($a->date) > new \DateTime($b->date);
        });
        $reportAbsenceEmployee = array_replace($reportEmployeeLowByDateEmployees, $reportEmployeePersonalAffairsByDateEmployees);

        if (!empty($reportFilter->send)) {
            $this->sendEmailsToEmployees($lastMonthEvents);
        }
        if (!empty($reportFilter->sendFichar)) {
            $this->sendFicharEmailsToEmployees($lastFichar);
        }

        $this->eventDispatcher->dispatchMultiple($action, $configuration, $resources);

        $view = View::create();

        if ($isPost) {
            $fromDateFilter = $reportFilter->fromDate;
            $toDateFilter = $reportFilter->toDate;

            $cookieFromDate = new Cookie('fromDate', $fromDateFilter->format('Y-m-d H:i:s'), strtotime('now + 30 days'));
            $cookieToDate = new Cookie('toDate', $toDateFilter->format('Y-m-d H:i:s'), strtotime('now + 30 days'));

            $response = $view->getResponse();
            $response->headers->setCookie($cookieFromDate);
            $response->headers->setCookie($cookieToDate);
        }

        if ($configuration->isHtmlRequest()) {
            $formView = $form->createView();
            $view
                ->setTemplate($configuration->getTemplate($action. '.html'))
                ->setTemplateVar($this->metadata->getPluralName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resources' => $resources,

                    'reportEventDatesPerEmployeeInt' => $reportEventDatesPerEmployeeInt,
                    'reportEventDatesPerEmployeeIntJSON' => json_encode($reportEventDatesPerEmployeeInt),

                    'reportEventDatesPerEmployeeSub' => $reportEventDatesPerEmployeeSub,
                    'reportEventDatesPerEmployeeSubJSON' => json_encode($reportEventDatesPerEmployeeSub),

                    'reportHoursPerEmployeeByMonth' => $reportHoursPerEmployeeByMonth,
                    'reportHoursPerEmployeeByMonthJSON' => json_encode($reportHoursPerEmployeeByMonth),

                    'reportCostPerEmployeeByEvent' => $reportCostPerEmployeeByEvent,
                    'reportCostPerEmployeeByEventJSON' => json_encode($reportCostPerEmployeeByEvent),

                    'lastEmployeeIdLow' => $lastEmployeeIdLow + 1,
                    'reportEmployeeLowByDateEmployees' => $reportEmployeeLowByDateEmployees,
                    'reportEmployeeLowByDate' => $reportEmployeeLowByDate,
                    'reportEmployeeLowByDateJSON' => json_encode($reportEmployeeLowByDate),

                    'lastEmployeeIdPersonalAffairs' => $lastEmployeeIdPersonalAffairs + 1,
                    'reportEmployeePersonalAffairsByDateEmployees' => $reportEmployeePersonalAffairsByDateEmployees,
                    'reportEmployeePersonalAffairsByDate' => $reportEmployeePersonalAffairsByDate,
                    'reportEmployeePersonalAffairsByDateJSON' => json_encode($reportEmployeePersonalAffairsByDate),

                    'lastAbseceEmployeeId' => $lastAbseceEmployeeId + 1,
                    'reportAbsenceEmployee' => $reportAbsenceEmployee,
                    'reportAbsence' => $reportAbsence,
                    'reportAbsenceJSON' => json_encode($reportAbsence),

                    'reportFicharByDate' => $reportFicharByDate,
                    'reportFicharByDateJSON' => json_encode($reportFicharByDate),

                    'reportFicharByDateAndDayListInt' => $reportFicharByDateAndDayListInt,
                    'reportFicharByDateAndDayListIntJSON' => json_encode($reportFicharByDateAndDayListInt),

                    'reportFicharByDateAndDayListSub' => $reportFicharByDateAndDayListSub,
                    'reportFicharByDateAndDayListSubJSON' => json_encode($reportFicharByDateAndDayListSub),

                    'reportFilter' => $reportFilter,
                    'form' => $formView,

                    $this->metadata->getPluralName() => $resources
                ])
            ;
        }

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }
}