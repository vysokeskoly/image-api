<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use VysokeSkoly\ImageApi\Facade\StorageFacade;
use VysokeSkoly\ImageApi\Service\NamespaceService;

class ListCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(private string $defaultNamespace, private NamespaceService $namespaceService, private StorageFacade $storage)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('image:list')
            ->setDescription('Lists all images in given namespace.')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Whether to just count images.')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'Namespace to list.', $this->defaultNamespace);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('List images');
        $onlyCount = (bool) $input->getOption('count');
        $namespace = (string) $input->getArgument('namespace');

        $this->namespaceService->useNamespace($namespace);

        if ($this->io->isVerbose()) {
            $this->io->section('Input');
            $this->io->table(['Input', 'Value'], [
                ['onlyCount', $onlyCount ? 'true' : 'false'],
                ['namespace', sprintf('%s (%s)', $namespace, $this->namespaceService->getNamespace())],
            ]);
        }

        $all = $this->storage->listAll();
        if ($onlyCount) {
            $this->io->writeln((string) count($all));
        } else {
            $this->io->listing($all);
        }

        return 0;
    }
}
