<?php

namespace App\Services\Parser\Instagram;

use App\Interfaces\ParserServiceInterface;

/**
 * Class AbstractInstagramParserService
 * @package App\Services\Parser\Instagram
 */
abstract class AbstractInstagramParserService implements ParserServiceInterface
{
    const INIT_LINK = 'https://www.instagram.com/marsego_lovesyou/';
    const API_LINK = 'https://www.instagram.com/graphql/query/?query_hash={hash}';//&variables={"id":"1694681456","first":12,"after":"{cursor}"}';
    const HASH_FILE_REGEX = '/src="(.*ProfilePageContainer\.js.*)"/U';
    const HASH_REGEX = '/="(.*)";.*createFeedLoadedAction=/Ui';

    protected $hash = '';

    /**
     * @return string
     */
    public function getInitPage(): string
    {
        return self::INIT_LINK;
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function getHashFile(string $html): string
    {
        if (preg_match_all(self::HASH_FILE_REGEX, $html, $matches)) {
            return 'https://www.instagram.com' . $matches[1][0];
        }

        return '';
    }

    /**
     * @param string $html
     *
     * @return bool
     */
    public function getHash(string $html): bool
    {
        if (preg_match_all(self::HASH_REGEX, $html, $matches)) {
            $this->hash = $matches[1][0];

            return true;
        }

        return false;
    }

    /**
     * @param string $html
     *
     * @return bool
     */
    public function hasNextPage(string $html): bool
    {
        if (preg_match_all('/"edge_owner_to_timeline_media":.*"page_info":{"has_next_page":(.*),/U', $html, $matches)) {
            return $matches[1][0] == 'true';
        }

        return false;
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function getCursor(string $html): string
    {
        if (preg_match_all('/"end_cursor":"(.*)"/U', $html, $matches)) {
            return $matches[1][0];
        }

        return '';
    }

    /**
     * @param string $cursor
     *
     * @return string
     */
    public function getApiLink(string $cursor): string
    {
        $result = str_replace('{hash}', $this->hash, self::API_LINK);
        $result = str_replace('{cursor}', $cursor, $result);

        return $result;
    }
}