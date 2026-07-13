<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:promote-user', description: 'Grant ROLE_ADMIN (or ROLE_SUPER_ADMIN) to a user by email')]
class PromoteUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email of the user to promote');
        $this->addOption('super', null, InputOption::VALUE_NONE, 'Grant ROLE_SUPER_ADMIN instead of ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $role  = $input->getOption('super') ? 'ROLE_SUPER_ADMIN' : 'ROLE_ADMIN';

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            $io->error(sprintf('No user found with email "%s".', $email));
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if (in_array($role, $roles, true)) {
            $io->warning(sprintf('User "%s" already has %s.', $email, $role));
            return Command::SUCCESS;
        }

        $roles[] = $role;
        $user->setRoles(array_values(array_unique($roles)));
        $this->em->flush();

        $io->success(sprintf('User "%s" promoted to %s.', $email, $role));
        return Command::SUCCESS;
    }
}
