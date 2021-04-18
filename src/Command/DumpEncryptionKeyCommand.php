<?php

declare(strict_types=1);

namespace App\Command;

use ParagonIE\Halite\KeyFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DumpEncryptionKeyCommand extends Command
{
    protected static $defaultName = 'app:dump-encryption-key';

    public function __construct(private string $encryptionKeyPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        if (file_exists($this->encryptionKeyPath)) {
            $symfonyStyle->warning('File at this path exists - refusing to override to avoid losing the existing key.');

            return Command::SUCCESS;
        }

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $this->encryptionKeyPath);

        $symfonyStyle->success('Key saved.');
        $symfonyStyle->warning('Run chmod 640 ' . $this->encryptionKeyPath);

        return Command::SUCCESS;
    }
}
