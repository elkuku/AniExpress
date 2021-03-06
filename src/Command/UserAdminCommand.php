<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnexpectedValueException;

#[AsCommand(
    name: 'user-admin',
    description: 'Administer user accounts',
    aliases: ['useradmin', 'admin']
)]
class UserAdminCommand extends Command
{
    private InputInterface $input;
    private OutputInterface $output;
    private SymfonyStyle $io;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('KuKu\'s User Admin');

        do {
            $exit = $this->showMenu();
        } while (!$exit);

        return Command::SUCCESS;
    }

    private function showMenu(): int
    {
        $answer = $this->getAnswer();
        $this->output->writeln($answer);

        try {
            switch ($answer) {
                case 'Create User':
                    $this->createUser();
                    $this->io->success('User created');
                    break;
                case 'Read Users':
                    $this->renderUsersTable();
                    break;
                case 'Update User':
                    $this->io->warning('Update is not implemented yet :(');
                    break;
                case 'Delete User':
                    $this->deleteUser();
                    $this->io->success('User has been removed');
                    break;
                case 'Exit':
                    $this->io->text('have Fun =;)');

                    return Command::FAILURE;
                default:
                    throw new UnexpectedValueException(
                        'Unknown answer: '.$answer
                    );
            }
        } catch (Exception $exception) {
            $this->io->error($exception->getMessage());
        }

        return Command::SUCCESS;
    }

    private function getAnswer(): string
    {
        $question = (new ChoiceQuestion(
            'Please select an option (defaults to exit)',
            [
                'Exit',
                'Create User',
                'Read Users',
                'Update User',
                'Delete User',
            ],
            0
        ))
            ->setErrorMessage('Choice %s is invalid.');

        return $this->getHelper('question')->ask(
            $this->input,
            $this->output,
            $question
        );
    }

    private function renderUsersTable(): void
    {
        $table = new Table($this->output);
        $table->setHeaders(['ID', 'Identifier', 'Role', 'GoogleId', 'GitHubId']);

        $users = $this->entityManager->getRepository(User::class)
            ->findBy([], ['id' => 'ASC']);

        $this->io->text(
            sprintf(
                '<fg=cyan>There are %d users in the database.</>',
                count($users)
            )
        );

        /* @type User $user */
        foreach ($users as $user) {
            $table->addRow(
                [
                    $user->getId(),
                    $user->getUserIdentifier(),
                    implode(', ', $user->getRoles()),
                    $user->getGoogleId(),
                    $user->getGitHubId(),
                ]
            );
        }
        $table->render();
    }

    private function askIdentifier(): string
    {
        $io = new SymfonyStyle($this->input, $this->output);
        do {
            $identifier = $this->getHelper('question')->ask(
                $this->input,
                $this->output,
                new Question('Identifier: ')
            );
            if (!$identifier) {
                $io->warning('Identifier required :(');
            }
        } while ($identifier === null);

        return $identifier;
    }

    private function askRole(): mixed
    {
        return $this->getHelper('question')->ask(
            $this->input,
            $this->output,
            (new ChoiceQuestion(
                'User role',
                array_values(User::ROLES)
            ))
                ->setErrorMessage('Choice %s is invalid.')
        );
    }

    private function createUser(): void
    {
        $user = (new User())
            ->setUserIdentifier($this->askIdentifier())
            ->setRole($this->askRole());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function deleteUser(): void
    {
        $id = $this->getHelper('question')->ask(
            $this->input,
            $this->output,
            new Question('User ID to delete: ')
        );
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(
                ['id' => $id]
            );

        if (!$user) {
            throw new UnexpectedValueException('User not found!');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
