<?php

/*
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\services\extension;

//use fecshop\models\mysqldb\cms\StaticBlock;
use Yii;
use fecshop\services\Service;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Administer extends Service
{
    
    /**
     * 1.插件的安装
     * @param $extension_name | string ， 插件名称（唯一）
     * @param $forceInstall | boolean ， 是否强制安装（即使安装了，还是强制执行安装的代码。）
     * @return boolean 安装成功返回的状态
     * 
     */
    public function install($extension_namespace, $forceInstall=false)
    {
        // 插件不存在
        $modelOne = Yii::$service->extension->getByNamespace($extension_namespace);
        if (!$modelOne['namespace']) {
            Yii::$service->helper->errors->add('extension: {namespace} is not exist', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        // 插件已经安装
        $installed_status = $modelOne['installed_status'];
        if (!$forceInstall && Yii::$service->extension->isInstalledStatus($installed_status)) {
            Yii::$service->helper->errors->add('extension: {namespace} has installed', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        
        // 通过数据库找到应用的配置文件路径
        $extensionConfigFile = Yii::getAlias($modelOne['config_file_path']);
        if (!file_exists($extensionConfigFile)) {
            Yii::$service->helper->errors->add('extension: {namespace} config file is not exit', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        // 加载应用配置
        $extensionConfig = require($extensionConfigFile);
        // 如果没有该配置，说明该插件不需要进行安装操作。
        if (!isset($extensionConfig['administer']['install'])) {
            Yii::$service->helper->errors->add('extension: {namespace}， have no install file function', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        
        // 事务操作, 只对mysql有效，如果是mongodb，无法回滚
        $innerTransaction = Yii::$app->db->beginTransaction();
        try {
            // 执行应用的install部分功能
            if (!Yii::$service->extension->installAddons($extensionConfig['administer']['install'], $modelOne)) {
                $innerTransaction->rollBack();
                
                return false;
            }
            $innerTransaction->commit();
            return true;
        } catch (\Exception $e) {
            $innerTransaction->rollBack();
            Yii::$service->helper->errors->add($e->getMessage());
        }
        
        return false;
    }
    
    /**
     * 应用升级函数
     * @param $extension_namespace | string , 插件的名称
     */
    public function upgrade($extension_namespace)
    {
        // 插件不存在
        $modelOne = Yii::$service->extension->getByNamespace($extension_namespace);
        if (!$modelOne['namespace']) {
            Yii::$service->helper->errors->add('extension: {namespace} is not exist', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        // 插件如果没有安装
        $installed_status = $modelOne['installed_status'];
        if (!Yii::$service->extension->isInstalledStatus($installed_status)) {
            Yii::$service->helper->errors->add('extension: {namespace} has not installed', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        
        // 通过数据库找到应用的配置文件路径，如果配置文件不存在
        $extensionConfigFile = Yii::getAlias($modelOne['config_file_path']);
        if (!file_exists($extensionConfigFile)) {
            Yii::$service->helper->errors->add('extension: {namespace} config file is not exit', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        // 加载应用配置
        $extensionConfig = require($extensionConfigFile);
        // 如果没有该配置，说明该插件不需要进行安装操作。
        if (!isset($extensionConfig['administer']['upgrade'])) {
            Yii::$service->helper->errors->add('extension: {namespace}， have no upgrade file function', ['namespace' =>$extension_namespace ]);
            
            return false;
        }
        
        // 事务操作, 只对mysql有效，如果是mongodb，无法回滚
        $innerTransaction = Yii::$app->db->beginTransaction();
        try {
            // 执行应用的upgrade部分功能
            if (!Yii::$service->extension->upgradeAddons($extensionConfig['administer']['upgrade'], $modelOne)) {
                $innerTransaction->rollBack();
                return false;
            }
            $innerTransaction->commit();
            
            return true;
        } catch (\Exception $e) {
            $innerTransaction->rollBack();
            Yii::$service->helper->errors->add($e->getMessage());
        }
        
        return false;
    }
    
    /**
     * 3.插件卸载。
     *
     */
    public function uninstall($extension_namespace)
    {
        
        
        
        
    }
    
    
    // 数据库的安装
    protected function installDbData($modelOne) 
    {
        
        
        
    }
    
    
    // theme文件进行copy到@app/theme/base/addons 下面。
    protected function  copyThemeFile($modelOne) 
    {
        
        
        
        
    }
    
    
}
