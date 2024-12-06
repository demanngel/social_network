<?php

namespace controllers;
class GroupsController {
    public function __construct() {}

    public function viewGroupsPage()
    {
        include 'groups.php';
    }

    public function showGroup($group_id) {
        include 'group.php';
    }
}