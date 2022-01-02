<?php

require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/config/permissions.php";

class PermissionsHelper
{

    public function isUserListEmployeesPermissionsGranted($userPermissions)
    {
        $isFounded = false;
        foreach($userPermissions as $permission) {
            if ($permission['permission'] == LIST_EMPLOYESS) {
                $isFounded = true;
                break;
            }
        }
        return $isFounded;
    }

    public function isUserAddEmployeesPermissionsGranted($userPermissions)
    {
        $isFounded = false;
        foreach($userPermissions as $permission) {
            if ($permission['permission'] == ADD_EMPLOYEES) {
                $isFounded = true;
                break;
            }
        }
        return $isFounded;
    }

    public function isUserDeactivateAnEmployeePermissionsGranted($userPermissions)
    {
        $isFounded = false;
        foreach($userPermissions as $permission) {
            if ($permission['permission'] == DEACTIVATE_AN_EMPLOYEE) {
                $isFounded = true;
                break;
            }
        }
        return $isFounded;
    }

    public function isUserUpdateContactInformationPermissionsGranted($userPermissions)
    {
        $isFounded = false;
        foreach($userPermissions as $permission) {
            if ($permission['permission'] == UPDATE_CONTACT_INFORMATION) {
                $isFounded = true;
                break;
            }
        }
        return $isFounded;
    }
}
