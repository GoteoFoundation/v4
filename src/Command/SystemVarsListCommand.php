<?php

namespace App\Command;

use App\Entity\SystemVar;
use App\Repository\SystemVarRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:system:vars:list'
)]
class SystemVarsListCommand extends Command
{
    public function __construct(
        private SystemVarRepository $systemVarRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->table(['Name', 'Value'], \array_map(function (SystemVar $var) {
            return [$var->getName(), $var->getValue()];
        }, $this->systemVarRepository->findAll()));

        return Command::SUCCESS;
    }
}
