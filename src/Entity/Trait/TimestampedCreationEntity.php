<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata as API;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampedCreationEntity
{
    /**
     * Entity creation timestamp.
     *
     * @var \DateTime|null
     */
    #[API\ApiProperty(writable: false)]
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected $dateCreated;

    /**
     * Sets dateCreated.
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Returns dateCreated.
     *
     * @return \DateTime|null
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}
