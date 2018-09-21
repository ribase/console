<?php

namespace Ribase\RibaseConsole\Helper;

class DatabaseExcludes
{
    /**
     * @var array
     */
    protected $noCacheExcludes = array(
        'cache_pages',
        'cache_extensions',
        'cache_hash',
        'link_cache',
        'cache_typo3temp_log',
        'cache_imagesizes',
        'cache_md5params',
        'cache_pages',
        'cache_pagesection',
        'cache_treelist',
        'cachingframework_cache_hash',
        'cachingframework_cache_hash_tags',
        'cachingframework_cache_pages',
        'cachingframework_cache_pages_tags',
        'cachingframework_cache_pagesection',
        'cachingframework_cache_pagesection_tags',
        'be_sessions',
        'fe_session_data',
        'fe_sessions',
        'index_fulltext',
        'index_grlist',
        'index_phash',
        'index_rel',
        'index_section',
        'index_stat_search',
        'index_stat_word',
        'index_words',
        'index_debug',
        'cf_cache_hash',
        'cf_cache_hash_tags',
        'cf_cache_news_category',
        'cf_cache_news_category_tags',
        'cf_cache_pages',
        'cf_cache_pagesection',
        'cf_cache_pagesection_tags',
        'cf_cache_pages_tags',
        'cf_cache_rootline',
        'cf_cache_rootline_tags',
        'cf_extbase_datamapfactory_datamap',
        'cf_extbase_datamapfactory_datamap_tags',
        'cf_extbase_object',
        'cf_extbase_object_tags',
        'cf_extbase_reflection',
        'cf_extbase_reflection_tags',
        'cf_extbase_typo3dbbackend_queries',
        'cf_extbase_typo3dbbackend_queries_tags',
        'cf_extbase_typo3dbbackend_tablecolumns',
        'cf_extbase_typo3dbbackend_tablecolumns_tags',
        'cf_fluidcontent',
        'cf_fluidcontent_tags',
        'cf_flux',
        'cf_flux_tags',
        'cf_vhs_main',
        'cf_vhs_main_tags',
        'cf_vhs_markdown',
        'cf_vhs_markdown_tags',
        'link_cache',
        'link_oldlinks',
        'tt_news_cache',
        'sys_history',
        'sys_log',
        'sys_dmail_maillog'
    );

    /**
     * @var array
     */
    protected $minimalIncludes = array(
        'tt_content',
        'pages',
        'pages_language_overlay',
        'sys_file',
        'sys_filemounts',
        'sys_file_collection',
        'sys_file_metadata',
        'sys_file_processedfile',
        'sys_file_reference',
        'sys_file_storage',
        'sys_registry',
        'sys_template'

    );

    public function createExcludes($type, $database) {

        $excludeString = "";

        if(empty($database) || empty($type)) {
            return false;
        }


        switch($type) {
            case "noCache":

                foreach ($this->noCacheExcludes as $value) {
                    $excludeString.= ' --ignore-table '.$database.'.'.$value;
                }
                break;
            default:
                $excludeString = "";
            break;
        }

        return $excludeString;

    }

    public function createIncludes($type, $database) {

        $includeString = "";

        if(empty($database) || empty($type)) {
            return false;
        }


        switch($type) {
            case "minimal":

                foreach ($this->minimalIncludes as $value) {
                    $includeString.= $value.' ';
                }
                break;
            default:
                $includeString = "";
                break;
        }

        return $includeString;

    }
}