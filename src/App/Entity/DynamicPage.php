<?php
/**
 * @file
 * Contains App\Entity\StaticPage.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Used for site static pages content.
 *
 * @ORM\Entity
 */
class DynamicPage
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=60, nullable=false, unique=true)
     * @var string
     */
    protected $alias;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $content;

    public function __construct($id, $alias, $title, $content)
    {
        $this->id = $id;
        $this->alias = $alias;
        $this->title = $title;
        $this->content = $content;
    }


    /**
     * @return string
     */
    public function id() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function alias() {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function title() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function content() {
        return $this->content;
    }
}