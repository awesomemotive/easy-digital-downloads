<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

use stdClass;

/**
 * Identifiers for the location used by various governments for tax purposes.
 */
class TaxIds implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $euVat;

    /**
     * @var string|null
     */
    private $frSiret;

    /**
     * @var string|null
     */
    private $frNaf;

    /**
     * @var string|null
     */
    private $esNif;

    /**
     * @var string|null
     */
    private $jpQii;

    /**
     * Returns Eu Vat.
     * The EU VAT number for this location. For example, `IE3426675K`.
     * If the EU VAT number is present, it is well-formed and has been
     * validated with VIES, the VAT Information Exchange System.
     */
    public function getEuVat(): ?string
    {
        return $this->euVat;
    }

    /**
     * Sets Eu Vat.
     * The EU VAT number for this location. For example, `IE3426675K`.
     * If the EU VAT number is present, it is well-formed and has been
     * validated with VIES, the VAT Information Exchange System.
     *
     * @maps eu_vat
     */
    public function setEuVat(?string $euVat): void
    {
        $this->euVat = $euVat;
    }

    /**
     * Returns Fr Siret.
     * The SIRET (Système d'Identification du Répertoire des Entreprises et de leurs Etablissements)
     * number is a 14-digit code issued by the French INSEE. For example, `39922799000021`.
     */
    public function getFrSiret(): ?string
    {
        return $this->frSiret;
    }

    /**
     * Sets Fr Siret.
     * The SIRET (Système d'Identification du Répertoire des Entreprises et de leurs Etablissements)
     * number is a 14-digit code issued by the French INSEE. For example, `39922799000021`.
     *
     * @maps fr_siret
     */
    public function setFrSiret(?string $frSiret): void
    {
        $this->frSiret = $frSiret;
    }

    /**
     * Returns Fr Naf.
     * The French government uses the NAF (Nomenclature des Activités Françaises) to display and
     * track economic statistical data. This is also called the APE (Activite Principale de l’Entreprise)
     * code.
     * For example, `6910Z`.
     */
    public function getFrNaf(): ?string
    {
        return $this->frNaf;
    }

    /**
     * Sets Fr Naf.
     * The French government uses the NAF (Nomenclature des Activités Françaises) to display and
     * track economic statistical data. This is also called the APE (Activite Principale de l’Entreprise)
     * code.
     * For example, `6910Z`.
     *
     * @maps fr_naf
     */
    public function setFrNaf(?string $frNaf): void
    {
        $this->frNaf = $frNaf;
    }

    /**
     * Returns Es Nif.
     * The NIF (Numero de Identificacion Fiscal) number is a nine-character tax identifier used in Spain.
     * If it is present, it has been validated. For example, `73628495A`.
     */
    public function getEsNif(): ?string
    {
        return $this->esNif;
    }

    /**
     * Sets Es Nif.
     * The NIF (Numero de Identificacion Fiscal) number is a nine-character tax identifier used in Spain.
     * If it is present, it has been validated. For example, `73628495A`.
     *
     * @maps es_nif
     */
    public function setEsNif(?string $esNif): void
    {
        $this->esNif = $esNif;
    }

    /**
     * Returns Jp Qii.
     * The QII (Qualified Invoice Issuer) number is a 14-character tax identifier used in Japan.
     * For example, `T1234567890123`.
     */
    public function getJpQii(): ?string
    {
        return $this->jpQii;
    }

    /**
     * Sets Jp Qii.
     * The QII (Qualified Invoice Issuer) number is a 14-character tax identifier used in Japan.
     * For example, `T1234567890123`.
     *
     * @maps jp_qii
     */
    public function setJpQii(?string $jpQii): void
    {
        $this->jpQii = $jpQii;
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
        if (isset($this->euVat)) {
            $json['eu_vat']   = $this->euVat;
        }
        if (isset($this->frSiret)) {
            $json['fr_siret'] = $this->frSiret;
        }
        if (isset($this->frNaf)) {
            $json['fr_naf']   = $this->frNaf;
        }
        if (isset($this->esNif)) {
            $json['es_nif']   = $this->esNif;
        }
        if (isset($this->jpQii)) {
            $json['jp_qii']   = $this->jpQii;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
