<?php

namespace Kaa\Component\Generator\Writer;

use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class BootstrapWriter
{
    private bool $initialized = false;
    private readonly string $fileName;

    public function __construct(
        private readonly string $exportDirectory
    ) {
        $this->fileName = $exportDirectory . '/bootstrap.php';
    }

    /**
     * @throws WriterException
     */
    public function append(string $code): self
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        file_put_contents($this->fileName, "\n" . $code, FILE_APPEND);

        return $this;
    }

    /**
     * @throws WriterException
     */
    private function initialize(): void
    {
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }

        if (
            !is_dir($this->exportDirectory)
            && !mkdir($this->exportDirectory, recursive: true)
            && !is_dir($this->exportDirectory)
        ) {
            throw new WriterException("Directory {$this->exportDirectory} was not created");
        }

        file_put_contents($this->fileName, "<?php \n");

        $this->initialized = true;
    }
}
