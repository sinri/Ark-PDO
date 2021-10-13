<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/7
 * Time: 14:37
 */

namespace sinri\ark\database\model;


/**
 * Class ArkDatabaseTableModel
 * @package sinri\ark\database\model
 * @since 1.7.0 the general methods are moved into core model
 * @deprecated since 2.1.x
 */
abstract class ArkDatabaseTableModel extends ArkDatabaseTableCoreModel
{

    /**
     * @return string
     * @since 1.6.2
     */
    public function mappingSchemeName(): string
    {
        return '';
    }

}