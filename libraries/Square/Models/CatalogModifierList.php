<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * For a text-based modifier, this encapsulates the modifier's text when its `modifier_type` is `TEXT`.
 * For example, to sell T-shirts with custom prints, a text-based modifier can be used to capture the
 * buyer-supplied
 * text string to be selected for the T-shirt at the time of sale.
 *
 * For non text-based modifiers, this encapsulates a non-empty list of modifiers applicable to items
 * at the time of sale. Each element of the modifier list is a `CatalogObject` instance of the
 * `MODIFIER` type.
 * For example, a "Condiments" modifier list applicable to a "Hot Dog" item
 * may contain "Ketchup", "Mustard", and "Relish" modifiers.
 *
 * A non text-based modifier can be applied to the modified item once or multiple times, if the
 * `selection_type` field
 * is set to `SINGLE` or `MULTIPLE`, respectively. On the other hand, a text-based modifier can be
 * applied to the item
 * only once and the `selection_type` field is always set to `SINGLE`.
 */
class CatalogModifierList implements \JsonSerializable
{
    /**
     * @var array
     */
    private $name = [];

    /**
     * @var array
     */
    private $ordinal = [];

    /**
     * @var string|null
     */
    private $selectionType;

    /**
     * @var array
     */
    private $modifiers = [];

    /**
     * @var array
     */
    private $imageIds = [];

    /**
     * @var string|null
     */
    private $modifierType;

    /**
     * @var array
     */
    private $maxLength = [];

    /**
     * @var array
     */
    private $textRequired = [];

    /**
     * @var array
     */
    private $internalName = [];

    /**
     * Returns Name.
     * The name of the `CatalogModifierList` instance. This is a searchable attribute for use in applicable
     * query filters, and its value length is of
     * Unicode code points.
     */
    public function getName(): ?string
    {
        if (count($this->name) == 0) {
            return null;
        }
        return $this->name['value'];
    }

    /**
     * Sets Name.
     * The name of the `CatalogModifierList` instance. This is a searchable attribute for use in applicable
     * query filters, and its value length is of
     * Unicode code points.
     *
     * @maps name
     */
    public function setName(?string $name): void
    {
        $this->name['value'] = $name;
    }

    /**
     * Unsets Name.
     * The name of the `CatalogModifierList` instance. This is a searchable attribute for use in applicable
     * query filters, and its value length is of
     * Unicode code points.
     */
    public function unsetName(): void
    {
        $this->name = [];
    }

    /**
     * Returns Ordinal.
     * The position of this `CatalogModifierList` within a list of `CatalogModifierList` instances.
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
     * The position of this `CatalogModifierList` within a list of `CatalogModifierList` instances.
     *
     * @maps ordinal
     */
    public function setOrdinal(?int $ordinal): void
    {
        $this->ordinal['value'] = $ordinal;
    }

    /**
     * Unsets Ordinal.
     * The position of this `CatalogModifierList` within a list of `CatalogModifierList` instances.
     */
    public function unsetOrdinal(): void
    {
        $this->ordinal = [];
    }

    /**
     * Returns Selection Type.
     * Indicates whether a CatalogModifierList supports multiple selections.
     */
    public function getSelectionType(): ?string
    {
        return $this->selectionType;
    }

    /**
     * Sets Selection Type.
     * Indicates whether a CatalogModifierList supports multiple selections.
     *
     * @maps selection_type
     */
    public function setSelectionType(?string $selectionType): void
    {
        $this->selectionType = $selectionType;
    }

    /**
     * Returns Modifiers.
     * A non-empty list of `CatalogModifier` objects to be included in the `CatalogModifierList`,
     * for non text-based modifiers when the `modifier_type` attribute is `LIST`. Each element of this list
     * is a `CatalogObject` instance of the `MODIFIER` type, containing the following attributes:
     * ```
     * {
     * "id": "{{catalog_modifier_id}}",
     * "type": "MODIFIER",
     * "modifier_data": {{a CatalogModifier instance>}}
     * }
     * ```
     *
     * @return CatalogObject[]|null
     */
    public function getModifiers(): ?array
    {
        if (count($this->modifiers) == 0) {
            return null;
        }
        return $this->modifiers['value'];
    }

    /**
     * Sets Modifiers.
     * A non-empty list of `CatalogModifier` objects to be included in the `CatalogModifierList`,
     * for non text-based modifiers when the `modifier_type` attribute is `LIST`. Each element of this list
     * is a `CatalogObject` instance of the `MODIFIER` type, containing the following attributes:
     * ```
     * {
     * "id": "{{catalog_modifier_id}}",
     * "type": "MODIFIER",
     * "modifier_data": {{a CatalogModifier instance>}}
     * }
     * ```
     *
     * @maps modifiers
     *
     * @param CatalogObject[]|null $modifiers
     */
    public function setModifiers(?array $modifiers): void
    {
        $this->modifiers['value'] = $modifiers;
    }

    /**
     * Unsets Modifiers.
     * A non-empty list of `CatalogModifier` objects to be included in the `CatalogModifierList`,
     * for non text-based modifiers when the `modifier_type` attribute is `LIST`. Each element of this list
     * is a `CatalogObject` instance of the `MODIFIER` type, containing the following attributes:
     * ```
     * {
     * "id": "{{catalog_modifier_id}}",
     * "type": "MODIFIER",
     * "modifier_data": {{a CatalogModifier instance>}}
     * }
     * ```
     */
    public function unsetModifiers(): void
    {
        $this->modifiers = [];
    }

    /**
     * Returns Image Ids.
     * The IDs of images associated with this `CatalogModifierList` instance.
     * Currently these images are not displayed on EDD\Vendor\Square products, but may be displayed in 3rd-party
     * applications.
     *
     * @return string[]|null
     */
    public function getImageIds(): ?array
    {
        if (count($this->imageIds) == 0) {
            return null;
        }
        return $this->imageIds['value'];
    }

    /**
     * Sets Image Ids.
     * The IDs of images associated with this `CatalogModifierList` instance.
     * Currently these images are not displayed on EDD\Vendor\Square products, but may be displayed in 3rd-party
     * applications.
     *
     * @maps image_ids
     *
     * @param string[]|null $imageIds
     */
    public function setImageIds(?array $imageIds): void
    {
        $this->imageIds['value'] = $imageIds;
    }

    /**
     * Unsets Image Ids.
     * The IDs of images associated with this `CatalogModifierList` instance.
     * Currently these images are not displayed on EDD\Vendor\Square products, but may be displayed in 3rd-party
     * applications.
     */
    public function unsetImageIds(): void
    {
        $this->imageIds = [];
    }

    /**
     * Returns Modifier Type.
     * Defines the type of `CatalogModifierList`.
     */
    public function getModifierType(): ?string
    {
        return $this->modifierType;
    }

    /**
     * Sets Modifier Type.
     * Defines the type of `CatalogModifierList`.
     *
     * @maps modifier_type
     */
    public function setModifierType(?string $modifierType): void
    {
        $this->modifierType = $modifierType;
    }

    /**
     * Returns Max Length.
     * The maximum length, in Unicode points, of the text string of the text-based modifier as represented
     * by
     * this `CatalogModifierList` object with the `modifier_type` set to `TEXT`.
     */
    public function getMaxLength(): ?int
    {
        if (count($this->maxLength) == 0) {
            return null;
        }
        return $this->maxLength['value'];
    }

    /**
     * Sets Max Length.
     * The maximum length, in Unicode points, of the text string of the text-based modifier as represented
     * by
     * this `CatalogModifierList` object with the `modifier_type` set to `TEXT`.
     *
     * @maps max_length
     */
    public function setMaxLength(?int $maxLength): void
    {
        $this->maxLength['value'] = $maxLength;
    }

    /**
     * Unsets Max Length.
     * The maximum length, in Unicode points, of the text string of the text-based modifier as represented
     * by
     * this `CatalogModifierList` object with the `modifier_type` set to `TEXT`.
     */
    public function unsetMaxLength(): void
    {
        $this->maxLength = [];
    }

    /**
     * Returns Text Required.
     * Whether the text string must be a non-empty string (`true`) or not (`false`) for a text-based
     * modifier
     * as represented by this `CatalogModifierList` object with the `modifier_type` set to `TEXT`.
     */
    public function getTextRequired(): ?bool
    {
        if (count($this->textRequired) == 0) {
            return null;
        }
        return $this->textRequired['value'];
    }

    /**
     * Sets Text Required.
     * Whether the text string must be a non-empty string (`true`) or not (`false`) for a text-based
     * modifier
     * as represented by this `CatalogModifierList` object with the `modifier_type` set to `TEXT`.
     *
     * @maps text_required
     */
    public function setTextRequired(?bool $textRequired): void
    {
        $this->textRequired['value'] = $textRequired;
    }

    /**
     * Unsets Text Required.
     * Whether the text string must be a non-empty string (`true`) or not (`false`) for a text-based
     * modifier
     * as represented by this `CatalogModifierList` object with the `modifier_type` set to `TEXT`.
     */
    public function unsetTextRequired(): void
    {
        $this->textRequired = [];
    }

    /**
     * Returns Internal Name.
     * A note for internal use by the business.
     *
     * For example, for a text-based modifier applied to a T-shirt item, if the buyer-supplied text of
     * "Hello, Kitty!"
     * is to be printed on the T-shirt, this `internal_name` attribute can be "Use italic face" as
     * an instruction for the business to follow.
     *
     * For non text-based modifiers, this `internal_name` attribute can be
     * used to include SKUs, internal codes, or supplemental descriptions for internal use.
     */
    public function getInternalName(): ?string
    {
        if (count($this->internalName) == 0) {
            return null;
        }
        return $this->internalName['value'];
    }

    /**
     * Sets Internal Name.
     * A note for internal use by the business.
     *
     * For example, for a text-based modifier applied to a T-shirt item, if the buyer-supplied text of
     * "Hello, Kitty!"
     * is to be printed on the T-shirt, this `internal_name` attribute can be "Use italic face" as
     * an instruction for the business to follow.
     *
     * For non text-based modifiers, this `internal_name` attribute can be
     * used to include SKUs, internal codes, or supplemental descriptions for internal use.
     *
     * @maps internal_name
     */
    public function setInternalName(?string $internalName): void
    {
        $this->internalName['value'] = $internalName;
    }

    /**
     * Unsets Internal Name.
     * A note for internal use by the business.
     *
     * For example, for a text-based modifier applied to a T-shirt item, if the buyer-supplied text of
     * "Hello, Kitty!"
     * is to be printed on the T-shirt, this `internal_name` attribute can be "Use italic face" as
     * an instruction for the business to follow.
     *
     * For non text-based modifiers, this `internal_name` attribute can be
     * used to include SKUs, internal codes, or supplemental descriptions for internal use.
     */
    public function unsetInternalName(): void
    {
        $this->internalName = [];
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
        if (!empty($this->name)) {
            $json['name']           = $this->name['value'];
        }
        if (!empty($this->ordinal)) {
            $json['ordinal']        = $this->ordinal['value'];
        }
        if (isset($this->selectionType)) {
            $json['selection_type'] = $this->selectionType;
        }
        if (!empty($this->modifiers)) {
            $json['modifiers']      = $this->modifiers['value'];
        }
        if (!empty($this->imageIds)) {
            $json['image_ids']      = $this->imageIds['value'];
        }
        if (isset($this->modifierType)) {
            $json['modifier_type']  = $this->modifierType;
        }
        if (!empty($this->maxLength)) {
            $json['max_length']     = $this->maxLength['value'];
        }
        if (!empty($this->textRequired)) {
            $json['text_required']  = $this->textRequired['value'];
        }
        if (!empty($this->internalName)) {
            $json['internal_name']  = $this->internalName['value'];
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
