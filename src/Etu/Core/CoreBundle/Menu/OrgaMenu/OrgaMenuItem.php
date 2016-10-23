<?php

namespace Etu\Core\CoreBundle\Menu\OrgaMenu;

/**
 * User menu item.
 */
class OrgaMenuItem
{
    /**
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $alertsCount;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var string
     */
    protected $translation;

    /**
     * @var array
     */
    protected $linkAttributes;

    /**
     * @var array
     */
    protected $itemAttributes;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var OrgaMenuBuilder
     */
    protected $builder;

    /**
     * @param OrgaMenuBuilder $builder
     * @param string          $translation
     */
    public function __construct(OrgaMenuBuilder $builder, $translation = '')
    {
        $this->builder = $builder;
        $this->icon = false;
        $this->alertsCount = 0;
        $this->position = 0;
        $this->linkAttributes = array();
        $this->itemAttributes = array();

        $this->setTranslation($translation);
    }

    /**
     * @return OrgaMenuBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param string $icon
     *
     * @return OrgaMenuItem
     */
    public function setIcon($icon)
    {
        $this->icon = (string) $icon;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasIcon()
    {
        return $this->icon !== false;
    }

    /**
     * @return bool|string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param int $alertsCount
     *
     * @return OrgaMenuItem
     */
    public function setAlertsCount($alertsCount)
    {
        $this->alertsCount = (int) $alertsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getAlertsCount()
    {
        return $this->alertsCount;
    }

    /**
     * Sets the role to use.
     *
     * @param string $role
     *
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Retrieves the currently set role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return OrgaMenuItem
     */
    public function setItemAttribute($key, $value)
    {
        $this->itemAttributes[(string) $key] = $value;

        return $this;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasItemAttribute($key)
    {
        return isset($this->itemAttributes[(string) $key]);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getItemAttribute($key)
    {
        if (!$this->hasItemAttribute((string) $key)) {
            return null;
        }

        return $this->itemAttributes[(string) $key];
    }

    /**
     * @return array
     */
    public function getItemAttributes()
    {
        return $this->itemAttributes;
    }

    /**
     * @return string
     */
    public function getItemAttributesString()
    {
        $string = '';

        foreach ($this->itemAttributes as $name => $value) {
            $string .= $name.'="'.$value.'" ';
        }

        return trim($string);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return OrgaMenuItem
     */
    public function setLinkAttribute($key, $value)
    {
        $this->linkAttributes[(string) $key] = $value;

        return $this;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasLinkAttribute($key)
    {
        return isset($this->linkAttributes[(string) $key]);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getLinkAttribute($key)
    {
        if (!$this->hasLinkAttribute((string) $key)) {
            return null;
        }

        return $this->linkAttributes[$key];
    }

    /**
     * @return array
     */
    public function getLinkAttributes()
    {
        return $this->linkAttributes;
    }

    /**
     * @return string
     */
    public function getLinkAttributesString()
    {
        $string = '';

        foreach ($this->linkAttributes as $name => $value) {
            $string .= $name.'="'.$value.'" ';
        }

        return trim($string);
    }

    /**
     * @param string $translation
     *
     * @return OrgaMenuItem
     */
    public function setTranslation($translation)
    {
        $this->translation = (string) $translation;

        return $this;
    }

    /**
     * @return string
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param string $url
     *
     * @return OrgaMenuItem
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param int $position
     *
     * @return OrgaMenuItem
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function isSeparator()
    {
        return false;
    }

    /**
     * @return OrgaMenuBuilder
     */
    public function end()
    {
        return $this->builder;
    }
}
