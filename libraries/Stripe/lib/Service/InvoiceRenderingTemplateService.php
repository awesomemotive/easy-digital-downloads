<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class InvoiceRenderingTemplateService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * List all templates, ordered by creation date, with the most recently created
     * template appearing first.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\InvoiceRenderingTemplate>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/invoice_rendering_templates', $params, $opts);
    }

    /**
     * Updates the status of an invoice rendering template to ‘archived’ so no new
     * EDD\Vendor\Stripe objects (customers, invoices, etc.) can reference it. The template can
     * also no longer be updated. However, if the template is already set on a EDD\Vendor\Stripe
     * object, it will continue to be applied on invoices generated by it.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\InvoiceRenderingTemplate
     */
    public function archive($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/invoice_rendering_templates/%s/archive', $id), $params, $opts);
    }

    /**
     * Retrieves an invoice rendering template with the given ID. It by default returns
     * the latest version of the template. Optionally, specify a version to see
     * previous versions.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\InvoiceRenderingTemplate
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/invoice_rendering_templates/%s', $id), $params, $opts);
    }

    /**
     * Unarchive an invoice rendering template so it can be used on new EDD\Vendor\Stripe objects
     * again.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\InvoiceRenderingTemplate
     */
    public function unarchive($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/invoice_rendering_templates/%s/unarchive', $id), $params, $opts);
    }
}