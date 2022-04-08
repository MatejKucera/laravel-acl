<?php

namespace MatejKucera\LaravelAcl;

use Closure;
use Illuminate\Support\Facades\Auth;

class AclMiddleware
{
    public function handle($request, Closure $next)
    {
        $routeUses = $request->route()->action['uses'];
        if($routeUses instanceof Closure) {
            return $next($request);
        }

        $uses = $request->route()->action['uses'];

        if(substr($uses, 0, 1) == '\\') {
            // Is external class
            // Strip first backlash
            $route = substr($uses, 1);
        } else {
            // Is internal class
            // Strip used route
            $route = explode('Controllers\\', $uses)[1];
        }

        // Separate controller and actions
        $routeSeparated = explode('@', $route);

        // Separate controller structure & action
        $controllerSeparated = explode('\\', substr($routeSeparated[0], 0, -10));
        $action = $routeSeparated[1];

        // Build config route
        $configRouteStack = "";
        foreach($controllerSeparated as $controllerRoute) {
            $configRouteStack .= $controllerRoute.".";
        }
        $configRouteStack .= $action; // add action at the end of controller route stack

        // Query config for allowed groups
        $allowedGroups = config('acl.permissions.'.$configRouteStack);

        if(!$allowedGroups) {
            $this->_deny();
        }

        $isAllowed = false; // prepared field which will be set to true if we are going to find adequate role
        $userGroups = Auth::guest() ? ['guest'] : Auth::user()->groups();

		// If the user is authenticated and is admin (and isAdmin is implemented), let him go
		if(!Auth::guest() && method_exists(Auth::user(), 'isAdmin') && Auth::user()->isAdmin()) {
			return $next($request);
		}

        if($this->_isAllowed($allowedGroups, $userGroups)) {
            return $next($request);
        } else {
            $this->_deny();
        }
    }

    /**
     * Action run on denied.
     */
    private function _deny() {
        if(config('acl.onDenied.abort')) {
            abort(config('acl.onDenied.code', config('acl.onDenied.message')));
        } else {
            config('acl.onDenied.function')();
        }
    }

    /**
     * Check if the user has any group that is in the list of allowed groups
     * @param $allowedGroups
     * @param $userGroups
     * @return bool
     */
    private function _isAllowed($allowedGroups, $userGroups) {
        // Iterate through allowed groups
        foreach($allowedGroups as $allowedGroup) {

            // Can everyone access?
            if($allowedGroup == 'everyone') {
                return true;
            }

            // Is it a class of groups?
            else if(strstr($allowedGroup, "class:")) {

                // Get class name
                $className = explode(":", $allowedGroup)[1];

                //Get allowed class groups from config
                $allowedClassGroups = config('acl.classes.'.$className);

                // Iterate through class groups and allow access if required group is found
                foreach($allowedClassGroups as $allowedClassGroup) {
                    if(in_array($allowedClassGroup, $userGroups)) {
                        return true;
                    }
                }
            }

            // It is normal group
            else {
                // Allow access if required group is found
                if(in_array($allowedGroup, $userGroups)) {
                    return true;
                }
            }
        }

        return false;
    }
}