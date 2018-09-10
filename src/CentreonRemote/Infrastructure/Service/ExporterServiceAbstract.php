<?php
namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;

abstract class ExporterServiceAbstract implements ExporterServiceInterface
{

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $db;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    protected $commitment;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get('centreon.db-manager');
    }

    public function setCommitment(ExportCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public function setManifest(ExportManifest $manifest): void
    {
        $this->manifest = $manifest;
    }

    public static function getName(): string
    {
        return static::NAME;
    }

    /**
     * Create path for export
     * 
     * @param string $exportPath
     * @return string
     */
    public function createPath(string $exportPath = null): string
    {
        // Create export path
        $exportPath = $this->getPath($exportPath);

        // make directory if missing
        if (!is_dir($exportPath)) {
            mkdir($exportPath, $this->commitment->getFilePermission(), true);
        }

        return $exportPath;
    }

    /**
     * Get path of export
     * 
     * @param string $exportPath
     * @return string
     */
    public function getPath(string $exportPath = null): string
    {
        $exportPath = $exportPath ?? $this->commitment->getPath() . '/' . $this->getName();

        return $exportPath;
    }

    /**
     * Get exported file
     * 
     * @param string $filename
     * @return string
     */
    public function getFile(string $filename): string
    {
        $exportFilepath = $this->getPath() . '/' . $filename;

        return $exportFilepath;
    }

    protected function _parse(string $filename): array
    {
        $result = $this->commitment->getParser()::parse($filename);

        return $result;
    }

    protected function _dump(array $input, string $filename): void
    {
        $this->commitment->getParser()::dump($input, $filename);
        
        $this->manifest->addFile($filename);
    }
}
