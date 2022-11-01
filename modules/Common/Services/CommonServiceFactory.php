<?php

namespace Modules\Common\Services;

use Modules\Common\Services\Impl\BankMessService;
use Modules\Common\Services\Impl\VersionService;
use Modules\Common\Services\Impl\SettingService;
use Modules\Common\Services\Impl\WarehouseService;
use Modules\Common\Services\Impl\ChinaWarehouseService;
use Modules\Common\Services\Impl\VipService;
use Modules\Common\Services\Impl\ServiceFeeService;
use Modules\Common\Services\Impl\TransportFeeService;
use Modules\Common\Services\Impl\InspectionFeeService;
use Modules\Common\Services\Impl\MediaService;
use Modules\Common\Services\Impl\UserService;
use Modules\Common\Services\Impl\RoleService;
use Modules\Common\Services\Impl\TransactionService;
use Modules\Common\Services\Impl\BankAccountService;
use Modules\Common\Services\Impl\WithdrawalRequestService;

class CommonServiceFactory
{
    protected static $mVersionService;
    protected static $mBankMessService;
    protected static $mSettingService;
    protected static $mWarehouseService;
    protected static $mChinaWarehouseService;
    protected static $mVipService;
    protected static $mServiceFeeService;
    protected static $mTransportFeeService;
    protected static $mInspectionFeeService;
    protected static $mMediaService;
    protected static $mUserService;
    protected static $mBankAccountService;
    protected static $mRoleService;
    protected static $mTransactionService;
    protected static $mWithdrawalRequestService;

    public static function mVersionService()
    {
        if (self::$mVersionService == null) {
            self::$mVersionService = new VersionService();
        }
        return self::$mVersionService;
    }

    public static function mBankMessService()
    {
        if (self::$mBankMessService == null) {
            self::$mBankMessService = new BankMessService();
        }
        return self::$mBankMessService;
    }

    public static function mSettingService()
    {
        if (self::$mSettingService == null) {
            self::$mSettingService = new SettingService();
        }
        return self::$mSettingService;
    }

    public static function mWarehouseService()
    {
        if (self::$mWarehouseService == null) {
            self::$mWarehouseService = new WarehouseService();
        }
        return self::$mWarehouseService;
    }

    public static function mChinaWarehouseService()
    {
        if (self::$mChinaWarehouseService == null) {
            self::$mChinaWarehouseService = new ChinaWarehouseService();
        }
        return self::$mChinaWarehouseService;
    }

    public static function mVipService()
    {
        if (self::$mVipService == null) {
            self::$mVipService = new VipService();
        }
        return self::$mVipService;
    }

    public static function mServiceFeeService()
    {
        if (self::$mServiceFeeService == null) {
            self::$mServiceFeeService = new ServiceFeeService();
        }
        return self::$mServiceFeeService;
    }

    public static function mTransportFeeService()
    {
        if (self::$mTransportFeeService == null) {
            self::$mTransportFeeService = new TransportFeeService();
        }
        return self::$mTransportFeeService;
    }

    public static function mInspectionFeeService()
    {
        if (self::$mInspectionFeeService == null) {
            self::$mInspectionFeeService = new InspectionFeeService();
        }
        return self::$mInspectionFeeService;
    }

    public static function mUserService()
    {
        if (self::$mUserService == null) {
            self::$mUserService = new UserService();
        }
        return self::$mUserService;
    }

    public static function mBankAccountService()
    {
        if (self::$mBankAccountService == null) {
            self::$mBankAccountService = new BankAccountService();
        }
        return self::$mBankAccountService;
    }

    public static function mMediaService()
    {
        if (self::$mMediaService == null) {
            self::$mMediaService = new MediaService();
        }
        return self::$mMediaService;
    }

    public static function mRoleService()
    {
        if (self::$mRoleService == null) {
            self::$mRoleService = new RoleService();
        }
        return self::$mRoleService;
    }

    public static function mTransactionService()
    {
        if (self::$mTransactionService == null) {
            self::$mTransactionService = new TransactionService();
        }
        return self::$mTransactionService;
    }

    public static function mWithdrawalRequestService()
    {
        if (self::$mWithdrawalRequestService == null) {
            self::$mWithdrawalRequestService = new WithdrawalRequestService();
        }
        return self::$mWithdrawalRequestService;
    }
}
