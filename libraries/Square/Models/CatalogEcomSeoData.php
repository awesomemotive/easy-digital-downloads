<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * SEO data for for a seller's EDD\Vendor\Square Online store.
 */
class CatalogEcomSeoData implements \JsonSerializable
{
    /**
     * @var array
     */
    private $pageTitle = [];

    /**
     * @var array
     */
    private $pageDescription = [];

    /**
     * @var array
     */
    private $permalink = [];

    /**
     * Returns Page Title.
     * The SEO title used for the EDD\Vendor\Square Online store.
     */
    public function getPageTitle(): ?string
    {
        if (count($this->pageTitle) == 0) {
            return null;
        }
        return $this->pageTitle['value'];
    }

    /**
     * Sets Page Title.
     * The SEO title used for the EDD\Vendor\Square Online store.
     *
     * @maps page_title
     */
    public function setPageTitle(?string $pageTitle): void
    {
        $this->pageTitle['value'] = $pageTitle;
    }

    /**
     * Unsets Page Title.
     * The SEO title used for the EDD\Vendor\Square Online store.
     */
    public function unsetPageTitle(): void
    {
        $this->pageTitle = [];
    }

    /**
     * Returns Page Description.
     * The SEO description used for the EDD\Vendor\Square Online store.
     */
    public function getPageDescription(): ?string
    {
        if (count($this->pageDescription) == 0) {
            return null;
        }
        return $this->pageDescription['value'];
    }

    /**
     * Sets Page Description.
     * The SEO description used for the EDD\Vendor\Square Online store.
     *
     * @maps page_description
     */
    public function setPageDescription(?string $pageDescription): void
    {
        $this->pageDescription['value'] = $pageDescription;
    }

    /**
     * Unsets Page Description.
     * The SEO description used for the EDD\Vendor\Square Online store.
     */
    public function unsetPageDescription(): void
    {
        $this->pageDescription = [];
    }

    /**
     * Returns Permalink.
     * The SEO permalink used for the EDD\Vendor\Square Online store.
     */
    public function getPermalink(): ?string
    {
        if (count($this->permalink) == 0) {
            return null;
        }
        return $this->permalink['value'];
    }

    /**
     * Sets Permalink.
     * The SEO permalink used for the EDD\Vendor\Square Online store.
     *
     * @maps permalink
     */
    public function setPermalink(?string $permalink): void
    {
        $this->permalink['value'] = $permalink;
    }

    /**
     * Unsets Permalink.
     * The SEO permalink used for the EDD\Vendor\Square Online store.
     */
    public function unsetPermalink(): void
    {
        $this->permalink = [];
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (!empty($this->pageTitle)) {
            $json['page_title']       = $this->pageTitle['value'];
        }
        if (!empty($this->pageDescription)) {
            $json['page_description'] = $this->pageDescription['value'];
        }
        if (!empty($this->permalink)) {
            $json['permalink']        = $this->permalink['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
