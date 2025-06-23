<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * References a text-based modifier or a list of non text-based modifiers applied to a `CatalogItem`
 * instance
 * and specifies supported behaviors of the application.
 */
class CatalogItemModifierListInfo implements \JsonSerializable
{
    /**
     * @var string
     */
    private $modifierListId;

    /**
     * @var array
     */
    private $modifierOverrides = [];

    /**
     * @var array
     */
    private $minSelectedModifiers = [];

    /**
     * @var array
     */
    private $maxSelectedModifiers = [];

    /**
     * @var array
     */
    private $enabled = [];

    /**
     * @var array
     */
    private $ordinal = [];

    /**
     * @param string $modifierListId
     */
    public function __construct(string $modifierListId)
    {
        $this->modifierListId = $modifierListId;
    }

    /**
     * Returns Modifier List Id.
     * The ID of the `CatalogModifierList` controlled by this `CatalogModifierListInfo`.
     */
    public function getModifierListId(): string
    {
        return $this->modifierListId;
    }

    /**
     * Sets Modifier List Id.
     * The ID of the `CatalogModifierList` controlled by this `CatalogModifierListInfo`.
     *
     * @required
     * @maps modifier_list_id
     */
    public function setModifierListId(string $modifierListId): void
    {
        $this->modifierListId = $modifierListId;
    }

    /**
     * Returns Modifier Overrides.
     * A set of `CatalogModifierOverride` objects that override whether a given `CatalogModifier` is
     * enabled by default.
     *
     * @return CatalogModifierOverride[]|null
     */
    public function getModifierOverrides(): ?array
    {
        if (count($this->modifierOverrides) == 0) {
            return null;
        }
        return $this->modifierOverrides['value'];
    }

    /**
     * Sets Modifier Overrides.
     * A set of `CatalogModifierOverride` objects that override whether a given `CatalogModifier` is
     * enabled by default.
     *
     * @maps modifier_overrides
     *
     * @param CatalogModifierOverride[]|null $modifierOverrides
     */
    public function setModifierOverrides(?array $modifierOverrides): void
    {
        $this->modifierOverrides['value'] = $modifierOverrides;
    }

    /**
     * Unsets Modifier Overrides.
     * A set of `CatalogModifierOverride` objects that override whether a given `CatalogModifier` is
     * enabled by default.
     */
    public function unsetModifierOverrides(): void
    {
        $this->modifierOverrides = [];
    }

    /**
     * Returns Min Selected Modifiers.
     * If 0 or larger, the smallest number of `CatalogModifier`s that must be selected from this
     * `CatalogModifierList`.
     * The default value is `-1`.
     *
     * When  `CatalogModifierList.selection_type` is `MULTIPLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that from zero to the maximum number of
     * modifiers of
     * the `CatalogModifierList` can be selected from the `CatalogModifierList`.
     *
     * When the `CatalogModifierList.selection_type` is `SINGLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that exactly one modifier must be
     * present in
     * and can be selected from the `CatalogModifierList`
     */
    public function getMinSelectedModifiers(): ?int
    {
        if (count($this->minSelectedModifiers) == 0) {
            return null;
        }
        return $this->minSelectedModifiers['value'];
    }

    /**
     * Sets Min Selected Modifiers.
     * If 0 or larger, the smallest number of `CatalogModifier`s that must be selected from this
     * `CatalogModifierList`.
     * The default value is `-1`.
     *
     * When  `CatalogModifierList.selection_type` is `MULTIPLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that from zero to the maximum number of
     * modifiers of
     * the `CatalogModifierList` can be selected from the `CatalogModifierList`.
     *
     * When the `CatalogModifierList.selection_type` is `SINGLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that exactly one modifier must be
     * present in
     * and can be selected from the `CatalogModifierList`
     *
     * @maps min_selected_modifiers
     */
    public function setMinSelectedModifiers(?int $minSelectedModifiers): void
    {
        $this->minSelectedModifiers['value'] = $minSelectedModifiers;
    }

    /**
     * Unsets Min Selected Modifiers.
     * If 0 or larger, the smallest number of `CatalogModifier`s that must be selected from this
     * `CatalogModifierList`.
     * The default value is `-1`.
     *
     * When  `CatalogModifierList.selection_type` is `MULTIPLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that from zero to the maximum number of
     * modifiers of
     * the `CatalogModifierList` can be selected from the `CatalogModifierList`.
     *
     * When the `CatalogModifierList.selection_type` is `SINGLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that exactly one modifier must be
     * present in
     * and can be selected from the `CatalogModifierList`
     */
    public function unsetMinSelectedModifiers(): void
    {
        $this->minSelectedModifiers = [];
    }

    /**
     * Returns Max Selected Modifiers.
     * If 0 or larger, the largest number of `CatalogModifier`s that can be selected from this
     * `CatalogModifierList`.
     * The default value is `-1`.
     *
     * When  `CatalogModifierList.selection_type` is `MULTIPLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that from zero to the maximum number of
     * modifiers of
     * the `CatalogModifierList` can be selected from the `CatalogModifierList`.
     *
     * When the `CatalogModifierList.selection_type` is `SINGLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that exactly one modifier must be
     * present in
     * and can be selected from the `CatalogModifierList`
     */
    public function getMaxSelectedModifiers(): ?int
    {
        if (count($this->maxSelectedModifiers) == 0) {
            return null;
        }
        return $this->maxSelectedModifiers['value'];
    }

    /**
     * Sets Max Selected Modifiers.
     * If 0 or larger, the largest number of `CatalogModifier`s that can be selected from this
     * `CatalogModifierList`.
     * The default value is `-1`.
     *
     * When  `CatalogModifierList.selection_type` is `MULTIPLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that from zero to the maximum number of
     * modifiers of
     * the `CatalogModifierList` can be selected from the `CatalogModifierList`.
     *
     * When the `CatalogModifierList.selection_type` is `SINGLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that exactly one modifier must be
     * present in
     * and can be selected from the `CatalogModifierList`
     *
     * @maps max_selected_modifiers
     */
    public function setMaxSelectedModifiers(?int $maxSelectedModifiers): void
    {
        $this->maxSelectedModifiers['value'] = $maxSelectedModifiers;
    }

    /**
     * Unsets Max Selected Modifiers.
     * If 0 or larger, the largest number of `CatalogModifier`s that can be selected from this
     * `CatalogModifierList`.
     * The default value is `-1`.
     *
     * When  `CatalogModifierList.selection_type` is `MULTIPLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that from zero to the maximum number of
     * modifiers of
     * the `CatalogModifierList` can be selected from the `CatalogModifierList`.
     *
     * When the `CatalogModifierList.selection_type` is `SINGLE`, `CatalogModifierListInfo.
     * min_selected_modifiers=-1`
     * and `CatalogModifierListInfo.max_selected_modifier=-1` means that exactly one modifier must be
     * present in
     * and can be selected from the `CatalogModifierList`
     */
    public function unsetMaxSelectedModifiers(): void
    {
        $this->maxSelectedModifiers = [];
    }

    /**
     * Returns Enabled.
     * If `true`, enable this `CatalogModifierList`. The default value is `true`.
     */
    public function getEnabled(): ?bool
    {
        if (count($this->enabled) == 0) {
            return null;
        }
        return $this->enabled['value'];
    }

    /**
     * Sets Enabled.
     * If `true`, enable this `CatalogModifierList`. The default value is `true`.
     *
     * @maps enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled['value'] = $enabled;
    }

    /**
     * Unsets Enabled.
     * If `true`, enable this `CatalogModifierList`. The default value is `true`.
     */
    public function unsetEnabled(): void
    {
        $this->enabled = [];
    }

    /**
     * Returns Ordinal.
     * The position of this `CatalogItemModifierListInfo` object within the `modifier_list_info` list
     * applied
     * to a `CatalogItem` instance.
     */
    public function getOrdinal(): ?int
    {
        if (count($this->ordinal) == 0) {
            return null;
        }
        return $this->ordinal['value'];
    }

    /**
     * Sets Ordinal.
     * The position of this `CatalogItemModifierListInfo` object within the `modifier_list_info` list
     * applied
     * to a `CatalogItem` instance.
     *
     * @maps ordinal
     */
    public function setOrdinal(?int $ordinal): void
    {
        $this->ordinal['value'] = $ordinal;
    }

    /**
     * Unsets Ordinal.
     * The position of this `CatalogItemModifierListInfo` object within the `modifier_list_info` list
     * applied
     * to a `CatalogItem` instance.
     */
    public function unsetOrdinal(): void
    {
        $this->ordinal = [];
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
        $json['modifier_list_id']           = $this->modifierListId;
        if (!empty($this->modifierOverrides)) {
            $json['modifier_overrides']     = $this->modifierOverrides['value'];
        }
        if (!empty($this->minSelectedModifiers)) {
            $json['min_selected_modifiers'] = $this->minSelectedModifiers['value'];
        }
        if (!empty($this->maxSelectedModifiers)) {
            $json['max_selected_modifiers'] = $this->maxSelectedModifiers['value'];
        }
        if (!empty($this->enabled)) {
            $json['enabled']                = $this->enabled['value'];
        }
        if (!empty($this->ordinal)) {
            $json['ordinal']                = $this->ordinal['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
