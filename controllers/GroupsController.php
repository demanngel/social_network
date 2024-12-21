<?php

namespace controllers;

require_once "./models/GroupModel.php";
require_once "./controllers/HeaderController.php";
require_once "./models/ApprovedPostsModel.php";
require_once "./models/SuggestedPostsModel.php";
require_once "./models/FilesModel.php";

use models\GroupModel;
use controllers\HeaderController;
use models\ApprovedPostsModel;
use models\SuggestedPostsModel;
use models\FilesModel;

class GroupsController {
    private $groupModel;
    private $headerController;
    private $suggestedPostsModel;
    private $approvedPostsModel;
    private $filesModel;

    public function __construct($conn) {
        $this->groupModel = new GroupModel($conn);
        $this->headerController = new HeaderController($conn);
        $this->approvedPostsModel = new ApprovedPostsModel($conn);
        $this->suggestedPostsModel = new SuggestedPostsModel($conn);
        $this->filesModel = new FilesModel($conn);
    }

    public function viewGroupsPage() {
        $this->headerController->viewHeader();

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'] ?? 'user';
        $search_term = $_GET['search'] ?? '';
        $search = '%' . $search_term . '%';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                header('Location: index.php?action=groups&error=invalid_token');
                exit();
            }

            if (isset($_POST['create_group'])) {
                $group_name = trim($_POST['group_name']);
                $group_description = trim($_POST['group_description']);
                
                if (!empty($group_name) && !empty($group_description)) {
                    $this->groupModel->createGroup($group_name, $group_description, $user_id);
                }
            }

            if (isset($_POST['join_group'])) {
                $group_id = intval($_POST['group_id']);
                $this->groupModel->joinGroup($group_id, $user_id);
            }

            if (isset($_POST['leave_group'])) {
                $group_id = intval($_POST['group_id']);
                $this->groupModel->leaveGroup($group_id, $user_id);
            }

            if (isset($_POST['delete_group']) && $user_role === 'moderator') {
                $group_id = intval($_POST['group_id']);
                $this->groupModel->deleteGroup($group_id, $user_id);
            }

            if (isset($_POST['edit_group']) && $user_role === 'moderator') {
                $group_id = intval($_POST['group_id']);
                $group_name = trim($_POST['group_name']);
                $group_description = trim($_POST['group_description']);
                
                if (!empty($group_name) && !empty($group_description)) {
                    $this->groupModel->updateGroup($group_id, $group_name, $group_description, $user_id);
                }
            }

            header('Location: index.php?action=groups');
            exit();
        }

        $groups = $this->groupModel->getGroups($user_id, $search, $user_role);
        include 'views/groups.php';
    }

    public function showGroup($group_id) {
        $this->headerController->viewHeader();

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'] ?? 'user';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                header('Location: index.php?action=group&id=' . $group_id . '&error=invalid_token');
                exit();
            }

            if (isset($_POST['join_group'])) {
                $this->groupModel->joinGroup(intval($_POST['group_id']), $user_id);
            }
            if (isset($_POST['leave_group'])) {
                $this->groupModel->leaveGroup(intval($_POST['group_id']), $user_id);
            }
            
            header('Location: index.php?action=group&id=' . $group_id);
            exit();
        }

        $group = $this->groupModel->getGroupById($group_id, $user_id);
        include 'views/group.php';
    }
}
