<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class FileService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of the files that your account has access to. EDD\Vendor\Stripe sorts and
     * returns the files by their creation dates, placing the most recently created
     * files at the top.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\File>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/files', $params, $opts);
    }

    /**
     * Retrieves the details of an existing file object. After you supply a unique file
     * ID, EDD\Vendor\Stripe returns the corresponding file object. Learn how to <a
     * href="/docs/file-upload#download-file-contents">access file contents</a>.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\File
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/files/%s', $id), $params, $opts);
    }

    /**
     * Create a file.
     *
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \EDD\Vendor\Stripe\File
     */
    public function create($params = null, $opts = null)
    {
        $opts = \EDD\Vendor\Stripe\Util\RequestOptions::parse($opts);
        if (!isset($opts->apiBase)) {
            $opts->apiBase = $this->getClient()->getFilesBase();
        }

        // Manually flatten params, otherwise curl's multipart encoder will
        // choke on nested null|arrays.
        $flatParams = \array_column(\EDD\Vendor\Stripe\Util\Util::flattenParams($params), 1, 0);

        return $this->request('post', '/v1/files', $flatParams, $opts);
    }
}
