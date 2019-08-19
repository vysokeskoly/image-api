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
    /** @var string */
    private $defaultNamespace;
    /** @var NamespaceService */
    private $namespaceService;
    /** @var StorageFacade */
    private $storage;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(string $defaultNamespace, NamespaceService $namespaceService, StorageFacade $storage)
    {
        $this->defaultNamespace = $defaultNamespace;
        $this->namespaceService = $namespaceService;
        $this->storage = $storage;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('image:list')
            ->setDescription('Lists all images in given namespace.')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'Whether to just count images.')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'Namespace to list.', $this->defaultNamespace);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
            $this->io->writeln(count($all));
        } else {
            $this->io->listing($all);
        }

        return 0;
    }
}
