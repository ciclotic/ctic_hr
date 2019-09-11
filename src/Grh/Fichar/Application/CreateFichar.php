<?php
namespace CTIC\Grh\Fichar\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Fichar\Domain\Command\FicharCommand;
use CTIC\Grh\Fichar\Domain\Fichar;

class CreateFichar implements CreateInterface
{
    /**
     * @param CommandInterface|FicharCommand $command
     * @return EntityInterface|Fichar
     *
     * @throws
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $fichar = new Fichar();
        $fichar->date = $command->date;
        $fichar->latitude = $command->latitude;
        $fichar->longitude = $command->longitude;
        if (!$fichar->setAction((int) $command->action)){
            throw new \Exception('No se ha definido la entrada o la salida correctamente.');
        }
        $fichar->setEmployee($command->employee);

        return $fichar;
    }
}