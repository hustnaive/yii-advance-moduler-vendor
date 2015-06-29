<?php
namespace yunke\helpers;
use yii\helpers\BaseUrl;
use yii;
/**
 * Url provides a set of static methods for managing URLs.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Url extends BaseUrl
{
    public static function toRoute($route, $scheme = false)
    {
    	$route[0]  = ltrim($route[0],'/');
    	if( substr_count($route[0], '/') == 2 ) $route[0] = '/'.$route[0];
    	return "/".I("__orgcode").parent::toRoute($route, $scheme);
    }

    protected static function normalizeRoute($route)
    {
        $route = (string) $route;
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return ltrim($route, '/');
        }

        // relative route
        if (Yii::$app->controller === null) {
            throw new InvalidParamException("Unable to resolve the relative route: $route. No active controller is available.");
        }

        if (strpos($route, '/') === false) {
            // empty or an action ID
            return $route === '' ? Yii::$app->controller->getRoute() : Yii::$app->controller->getUniqueId() . '/' . $route;
        } else {


            if(Yii::$app->controller->module->module->id == 'admin')
            {
                return $route;
            }
            else
            {
                // relative to module
                return ltrim(Yii::$app->controller->module->getUniqueId() . '/' . $route, '/');
            }
        }
    }


}
