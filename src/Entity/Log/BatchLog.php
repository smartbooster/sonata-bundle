<?php

namespace Smart\SonataBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Louis Fortunier <louis.fortunier@smartbooster.io>
 *
 * @ORM\Table(name="batch_log")
 * @ORM\Entity(repositoryClass="Smart\SonataBundle\Repository\Log\BatchLogRepository")
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
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private \DateTime $date;

    /**
     * @ORM\Column(name="context", type="string", nullable=false)
     */
    private string $context;

    /**
     * Name of the batch action
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private ?string $summary = null;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private ?string $comment = null;

    /**
     * @ORM\Column(name="data", type="json", nullable=true)
     */
    private ?array $data = null;

    /**
     * @ORM\Column(name="success", type="boolean", nullable=true)
     */
    private ?bool $success = null;

    public function __construct()
    {
        $this->setDate(new \DateTime());
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
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
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
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
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
