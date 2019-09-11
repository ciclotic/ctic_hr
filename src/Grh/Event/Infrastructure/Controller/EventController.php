<?php

namespace CTIC\Grh\Event\Infrastructure\Controller;

use CTIC\App\Base\Domain\EntityActions;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\Base\Infrastructure\Controller\ResourceController;
use CTIC\App\Base\Infrastructure\Request\RequestConfiguration;
use CTIC\App\Base\Infrastructure\View\Rest\View;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Event\Application\CreateEventEmployee;
use CTIC\Grh\Event\Domain\Command\EventEmployeeCommand;
use CTIC\Grh\Event\Domain\Event;
use CTIC\Grh\Event\Domain\EventEmployee;
use CTIC\Grh\Event\Infrastructure\Helper\EventHelper;
use CTIC\Grh\Event\Infrastructure\Repository\EventEmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EventController extends ResourceController
{
    /**
     * @param EntityInterface|Event $resource
     * @param RequestConfiguration $configuration
     */
    protected function completeEventEmployeeEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $eventFrom = $configuration->getRequest()->get('ctic_app_event');
        $eventEmployeesForm = $eventFrom['eventEmployee'];

        if (is_array($eventEmployeesForm)) {
            $em = $this->manager;
            /** @var EventEmployeeRepository $eventEmployeeRepository */
            $eventEmployeeRepository = $em->getRepository(EventEmployee::class);
            $employeeRepository = $em->getRepository(Employee::class);
            $eventEmployees = array();
            foreach ($eventEmployeesForm as $eventEmployeeForm) {
                if(!is_array($eventEmployeeForm) || empty($eventEmployeeForm['value']) || !isset($eventEmployeeForm['turn']))
                {
                    continue;
                }

                $eventEmployeeCommand = new EventEmployeeCommand();
                $eventEmployeeCommand->turn = $eventEmployeeForm['turn'];
                $employees[$eventEmployeeForm['value']] = $employeeRepository->find($eventEmployeeForm['value']);
                if (!is_object($employees[$eventEmployeeForm['value']]) || !($employees[$eventEmployeeForm['value']] instanceof Employee)) {
                    continue;
                }
                $eventEmployeeCommand->employee = $employees[$eventEmployeeForm['value']];
                $eventId = $resource->getId();
                if (empty($eventId)) {
                    $eventEmployeeCommand->id = rand(1000000, 2000000);
                    $eventEmployees[$eventEmployeeCommand->id] = CreateEventEmployee::create($eventEmployeeCommand);
                } else {
                    /** @var EventEmployee|null $newEventEmployee */
                    $newEventEmployee = $eventEmployeeRepository->findBy(array('event' => $resource, 'employee' => $employees[$eventEmployeeForm['value']]));
                    if (!is_object($newEventEmployee) || get_class($newEventEmployee) != EventEmployee::class) {
                        $eventEmployeeCommand->id = rand(1000000, 2000000);
                        $eventEmployees[$eventEmployeeCommand->id] = CreateEventEmployee::create($eventEmployeeCommand);
                    } else {
                        $eventEmployeeCommand->id = $newEventEmployee->getId();
                        $eventEmployees[$eventEmployeeCommand->id] = $newEventEmployee;
                        $eventEmployees[$eventEmployeeCommand->id]->setTurn($eventEmployeeCommand->turn);
                    }
                }

                $eventEmployeeRepository->add($eventEmployees[$eventEmployeeCommand->id]);
            }

            $currentEventEmployees = $resource->getEventEmployee();
            if (is_object($currentEventEmployees) && get_class($currentEventEmployees) == PersistentCollection::class) {
                foreach ($currentEventEmployees as $currentEventEmployee) {
                    if (!in_array($currentEventEmployee->getId(), array_keys($eventEmployees))) {
                        $eventEmployeeRepository->remove($currentEventEmployee);
                    }
                }
            }

            $eventEmployeesToResource = new ArrayCollection();
            foreach ($eventEmployees as $eventEmployee) {
                $eventEmployeesToResource->add($eventEmployee);
            }
            $resource->setEventEmployee($eventEmployeesToResource);
        }
    }

    /**
     * @param EntityInterface|Event $resource
     * @param RequestConfiguration $configuration
     */
    protected function completeCreateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $resource->creationDate = new \DateTime();
        $this->completeEventEmployeeEntity($resource, $configuration);
    }

    /**
     * @param EntityInterface|Event $resource
     * @param RequestConfiguration $configuration
     */
    protected function completeUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $this->completeEventEmployeeEntity($resource, $configuration);
    }

    /**
     * @param EntityInterface|Event $resource
     * @param RequestConfiguration $configuration
     */
    protected function prepareUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
    }

    /**
     * @param EntityInterface|Event $resource
     * @param RequestConfiguration $configuration
     */
    protected function postEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $em = $this->manager;
        /** @var EventEmployeeRepository $eventEmployeeRepository */
        $eventEmployeeRepository = $em->getRepository(EventEmployee::class);
        $eventEmployees = $resource->getEventEmployee();
        foreach ($eventEmployees as $eventEmployee) {
            if(!is_object($eventEmployee) || get_class($eventEmployee) != EventEmployee::class)
            {
                continue;
            }

            $eventEmployee->setEvent($resource);

            $eventEmployeeRepository->add($eventEmployee);
        }
    }

    /**
     * @param EntityInterface|Event $resource
     * @param RequestConfiguration $configuration
     */
    protected function postCreateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $this->postEntity($resource, $configuration);
    }

    /**
     * @param EntityInterface|Event $resource
     * @param RequestConfiguration $configuration
     */
    protected function postUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $this->postEntity($resource, $configuration);
    }

    /**
     * Returns an authorized API client.
     *
     * @param RequestConfiguration $configuration
     * @param string $authCode
     *
     * @return \Google_Client|View the authorized client object
     *
     * @throws
     */
    protected function getClient(RequestConfiguration $configuration, $authCode = null)
    {
        $session = $configuration->getRequest()->getSession();

        $identity = $session->get('identity');
        $dbName = $session->get('dbName');
        if (empty($identity) || empty($identity['data']['id']) || empty($dbName)) {
            throw new \Exception('Cannot get identity to google calendar');
        }
        $userId = $identity['data']['id'];

        $client = new \Google_Client();
        $client->setApplicationName('Gestión RRHH cicloTIC');
        $client->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'credentials/' . $dbName . '/' . $userId . '/googleCalendarToken.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                if (empty($authCode)) {
                    // Request authorization from the user.
                    $authUrl = $client->createAuthUrl();
                    $view = View::create();

                    if ($configuration->isHtmlRequest()) {
                        $view
                            ->setTemplate($configuration->getTemplate($this->getResourceName() . '/calendarAuthCode.html'))
                            ->setTemplateVar($this->metadata->getName())
                            ->setData([
                                'configuration' => $configuration,
                                'metadata' => $this->metadata,
                                'authUrl' => $authUrl
                            ])
                        ;
                    }
                    return $view;
                }

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new \Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }

    /**
     * @param RequestConfiguration $configuration
     * @param int $qty
     * @param string $authCode
     *
     * @return array|\Google_Service_Calendar_Event[]|View
     *
     * @throws \Exception
     */
    protected function getEventsFromGoogleCalendar(RequestConfiguration $configuration, $qty = 30, $authCode = null)
    {
        // Get the API client and construct the service object.
        $client = $this->getClient($configuration, $authCode);
        if (get_class($client) == View::class) {
            return $client;
        }
        $service = new \Google_Service_Calendar($client);

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => $qty,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        /** @var array $events */
        $events = $results->getItems();

        if (empty($events)) {
            return array();
        } else {
            return $events;
        }
    }

    /**
     * @param RequestConfiguration $configuration
     * @param string $iCalUID
     *
     * @return array|\Google_Service_Calendar_Event[]|View
     *
     * @throws \Exception
     */
    protected function getEventByICalUIDFromGoogleCalendar(RequestConfiguration $configuration, $iCalUID)
    {
        // Get the API client and construct the service object.
        $client = $this->getClient($configuration);
        if (get_class($client) == View::class) {
            return $client;
        }
        $service = new \Google_Service_Calendar($client);

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 1,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
            'iCalUID' => $iCalUID,
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        /** @var array $events */
        $events = $results->getItems();

        if (empty($events)) {
            return array();
        } else {
            return $events;
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws
     */
    public function calendarAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if (!$this->isGrantedOr403($configuration)) {
            return $this->redirectToLogin($configuration);
        }

        $authCode = $request->get('authCode', null);

        $events = $this->getEventsFromGoogleCalendar($configuration, 30, $authCode);
        if (is_object($events) && get_class($events) == View::class) {
            return $this->viewHandler->handle($events, $configuration->getRequest());
        }

        $iCalUIDs = array();
        foreach ($events as $event) {
            $event->start->datetime = new \DateTime($event->start->datetime);
            $iCalUIDs[] = $event->getICalUID();
        }

        /** @var Event[] $eventsInGrh */
        $eventsInGrh = $this->repository->findBy(array('iCalUID' => $iCalUIDs));

        $iCalUIDsInGrh = array();
        foreach ($eventsInGrh as $eventInGrh) {
            $iCalUIDsInGrh[$eventInGrh->getICalUID()] = $eventInGrh;
        }

        $view = View::create();

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate($this->getResourceName() . '/calendar.html'))
                ->setTemplateVar($this->metadata->getName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'events' => $events,
                    'iCalUIDsInGrh' => $iCalUIDsInGrh
                ])
            ;
        }

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws
     */
    public function createAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }
        /** @var Event $newResource */
        $newResource = $this->newResourceFactory->create($this->command);
        $this->prepareCreateEntity($newResource, $configuration);

        $iCalUID = $request->get('iCalUID');
        if (empty($iCalUID)) {
            $newResource->fromDate = new \DateTime(date('Y-m-d 00:45:00'));
            $newResource->shiftChangeDate = new \DateTime(date('Y-m-d 00:45:00'));
            $newResource->toDate = new \DateTime(date('Y-m-d 06:30:00'));
        } else {
            $event = $this->getEventByICalUIDFromGoogleCalendar($configuration, $iCalUID);
            if (is_object($event) && get_class($event) == View::class) {
                return $this->viewHandler->handle($event, $configuration->getRequest());
            }

            if(is_array($event))
            {
                $event = array_pop($event);
            }

            /** @var \Google_Service_Calendar_Event $event */
            $newResource->name = $event->getSummary();
            $newResource->description = $event->getDescription();
            $newResource->iCalUID = $event->getICalUID();
            $newResource->fromDate = new \DateTime($event->getStart()->date ?: $event->getStart()->dateTime);
            $newResource->shiftChangeDate = new \DateTime($event->getStart()->date ?: $event->getStart()->dateTime);
            $newResource->toDate = new \DateTime($event->getEnd()->date ?: $event->getEnd()->dateTime);
        }

        $form = $this->resourceFormFactory->create($configuration, $newResource);

        if ($request->isMethod('POST') && $form->handleRequest()->isValid()) {
            $newResource = $form->getData();

            $this->completeCreateEntity($newResource, $configuration);

            $event = $this->eventDispatcher->dispatchPreEvent(EntityActions::CREATE, $configuration, $newResource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                if ($event->hasResponse()) {
                    return $event->getResponse();
                }

                return $this->redirectHandler->redirectToIndex($configuration, $newResource);
            }

            if ($configuration->hasStateMachine()) {
                $this->stateMachine->apply($configuration, $newResource);
            }

            $this->repository->add($newResource);
            $this->postUpdateEntity($newResource, $configuration);
            $postEvent = $this->eventDispatcher->dispatchPostEvent(EntityActions::CREATE, $configuration, $newResource);

            if (!$configuration->isHtmlRequest()) {
                return $this->viewHandler->handle(View::create($newResource, Response::HTTP_CREATED), $configuration->getRequest());
            }

            $this->flashHelper->addSuccessFlash($configuration, EntityActions::CREATE, $newResource);

            if ($postEvent->hasResponse()) {
                return $postEvent->getResponse();
            }

            return $this->redirectHandler->redirectToIndex($configuration);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle(View::create($form, Response::HTTP_BAD_REQUEST), $configuration->getRequest());
        }

        $this->eventDispatcher->dispatchInitializeEvent(EntityActions::CREATE, $configuration, $newResource);

        $formView = $form->createView();
        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $newResource,
                $this->metadata->getName() => $newResource,
                'form' => $formView,
            ])
            ->setTemplate($configuration->getTemplate($this->getResourceName() . '/' . EntityActions::CREATE . '.html'))
        ;

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws
     */
    public function showAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }
        /** @var Event $resource */
        $resource = $this->findOr404($configuration);

        $this->eventDispatcher->dispatch(EntityActions::SHOW, $configuration, $resource);

        $view = View::create($resource);

        $resumeEmployeesByCategory = EventHelper::getResumeEmployeesByCategoryAction($resource);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate($this->getResourceName() . '/' . EntityActions::SHOW . '.html'))
                ->setTemplateVar($this->metadata->getName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resource' => $resource,
                    'resumeEmployeesByCategory' => $resumeEmployeesByCategory,
                    $this->metadata->getName() => $resource,
                ])
            ;
        }

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws
     */
    public function getAvailableEmployeesAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }

        $date = $request->get('date');
        if (empty($date)) {
            $date = new \DateTime('now');
        } else {
            $date = new \DateTime($date);
        }

        $er = $this->manager->getRepository(Employee::class);

        $qb = $er->createQueryBuilder('e')
            ->where('e.enabled = 1 AND el.id is NULL AND epa.id is NULL') //
            ->leftJoin('e.employeeLow', 'el', 'WITH', 'el.toDate > :now')
            ->leftJoin('e.employeePersonalAffairs', 'epa', 'WITH', 'epa.toDate > :now')
            ->setParameter('now', $date)
            ->orderBy('e.id', 'ASC')
            ->getQuery();
        $employees = $qb->execute();

        $return = array();
        /** @var Employee $employee */
        foreach ($employees as $employee) {
            $return[] = $employee->getName() . ' ' . $employee->getSurname();
        }

        $response = new Response(json_encode($return));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}