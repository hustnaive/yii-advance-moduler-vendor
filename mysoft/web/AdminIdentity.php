<?php
/**
 * Created by JetBrains PhpStorm.
 * User: owen
 * Date: 15-5-13
 * Time: 下午5:35
 * To change this template use File | Settings | File Templates.
 */


namespace yunke\web;

use services\pub\MyUserService;
use yii\web\IdentityInterface;

class AdminIdentity implements IdentityInterface {

    public $usercode;
    public $username;
    public $buguid;
    public $isAdmin;

    public $unitname;
    public $orgId;

    /**
     * @param $entity
     * @return KfsAdminIdentity|null
     */
    private static function convertModel($entity)
    {
        if (!isset($entity))
        {
            return null;
        }

        $user = new AdminIdentity();
        $user->usercode = $entity['UserCode'];
        $user->username = $entity['UserName'];
        $user->userGUID = $entity['UserGUID'];
        $user->buguid = $entity['BUGUID'];
        $user->isAdmin = $entity['IsAdmin'] == 1;
        return $user;
    }

    /**
     * 由于是开发商后台，多库的用户登录，所以$id的格式为：usercode@@unitname
     *
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        $partArr = explode("@@", $id);
        if(count($partArr) != 2)
            return null;
        $uid = $partArr[0];
        $unit = $partArr[1];

        $MyUser = new MyUserService($unit);
        $userEntity = $MyUser->findUser($uid);

        $user = self::convertModel($userEntity);
        if(is_null($user)) return $user;
        $user->unitname = $unit;
        return $user;
    }

    public static function clearCache($id){
        //CacheManage::deleteUserIdentityCache($id);
    }

    public static function afterLogout($event){
        //$identity = $event->identity;
        //CacheManage::deleteUserIdentityCache($identity->getId());
    }

  /*  public static function getUserAllRoleActions($userGUID){
        $userService = \Yii::createObject(\common\services\usermanage\UserService::className());
        $roleService = \Yii::createObject(\common\services\usermanage\RoleService::className());

        $roleIdArr = $userService->getUserRolesArray($userGUID);
        $actionModelArr = $roleService->getRollAllActions($roleIdArr);
        return $actionModelArr;
    }*/

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $data = self::decodeToken($token);
        $user = self::findIdentity($data["id"]);
        return $user;
    }

    private static function decodeToken($token){
        return array(
            "id"=>$token
        );
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->usercode."@@".$this->unitname;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->usercode;
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        $data = self::decodeToken($authKey);
        return $data["id"] == $this->getId();
    }
}