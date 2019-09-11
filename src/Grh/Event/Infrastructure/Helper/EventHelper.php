<?php
namespace CTIC\Grh\Event\Infrastructure\Helper;

use CTIC\Grh\Event\Domain\Event;

class EventHelper
{
    /**
     * @param Event $event
     *
     * @return array
     *
     * @throws
     */
    public static function getResumeEmployeesByCategoryAction(Event $event): array
    {
        $resumeEmployeesByCategory = array();

        $eventEmployees = $event->getEventEmployee();
        foreach ($eventEmployees as $eventEmployee) {
            $categoriesEmployee = $eventEmployee->getEmployee()->getEmployeeCategory();
            foreach ($categoriesEmployee as $categoryEmployee) {
                $categoryId = $categoryEmployee->getId();
                if (empty($resumeEmployeesByCategory[$categoryId])) {
                    $resumeEmployeesByCategory[$categoryId] = new \stdClass();
                    $resumeEmployeesByCategory[$categoryId]->name = $categoryEmployee->getName();
                    $resumeEmployeesByCategory[$categoryId]->employees = 0;
                }
                $resumeEmployeesByCategory[$categoryId]->employees += 1;
            }
        }

        return $resumeEmployeesByCategory;
    }
}