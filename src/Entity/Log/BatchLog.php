<?php

namespace Smart\SonataBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Louis Fortunier <louis.fortunier@smartbooster.io>
 *
 * @ORM\Table(name="batch_log")
 * @ORM\Entity()
 */
class BatchLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(name="context", type="string", nullable=false)
     */
    private string $context;

    /**
     * Name of the batch action
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private ?string $summary = null;

    /**
     * @ORM\Column(name="raw_data", type="text", nullable=true)
     */
    private ?string $rawData = null;

    /**
     * @ORM\Column(name="success", type="boolean", nullable=true)
     */
    private ?bool $success = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @param string|null $summary
     */
    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    /**
     * @return string|null
     */
    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    /**
     * @param string|null $rawData
     */
    public function setRawData(?string $rawData): void
    {
        $this->rawData = $rawData;
    }

    /**
     * @return bool|null
     */
    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    /**
     * @param bool|null $success
     */
    public function setSuccess(?bool $success): void
    {
        $this->success = $success;
    }
}
