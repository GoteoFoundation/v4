<?php

namespace App\Command;

use App\Entity\SystemVar;
use App\Repository\SystemVarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:system:vars:set',
    description: 'Set the value for a system var',
)]
class SystemVarsSetCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SystemVarRepository $systemVarRepository,
        private ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the system var')
            ->addArgument('value', InputArgument::REQUIRED, 'The value of the system var');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        $var = $this->systemVarRepository->findOneBy(['name' => $name]);
        if (!$var) {
            $var = new SystemVar;
        }

        $var->setName($name);
        $var->setValue($input->getArgument('value'));

        $errors = $this->validator->validate($var);
        if (count($errors) > 0) {
            $io->error((string) $errors);

            return Command::FAILURE;
        }

        $this->entityManager->persist($var);
        $this->entityManager->flush();

        $io->table(['Name', 'Value'], [[$var->getName(), $var->getValue()]]);

        return Command::SUCCESS;
    }
}
