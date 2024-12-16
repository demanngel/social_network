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
            if (isset($_POST['join_group'])) {
                $this->groupModel->joinGroup(intval($_POST['group_id']), $user_id);
            }
            if (isset($_POST['leave_group'])) {
                $this->groupModel->leaveGroup(intval($_POST['group_id']), $user_id);
            }
            if ($user_role === 'moderator') {
                if (isset($_POST['create_group'])) {
                    $this->groupModel->createGroup($_POST['group_name'], $_POST['group_description'], $user_id);
                }
                if (isset($_POST['delete_group'])) {
                    $this->groupModel->deleteGroup(intval($_POST['group_id']), $user_id);
                }
            }
        }

        $groups = $this->groupModel->getGroups($user_id, $search, $user_role);
        include 'views/groups.php';
    }

    public function showGroup($group_id) {
        $this->headerController->viewHeader();

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'] ?? 'user';
        $search_term = $_GET['search'] ?? '';
        $search = '%' . $search_term . '%';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['join_group'])) {
                $this->groupModel->joinGroup(intval($_POST['group_id']), $user_id);
            }
            if (isset($_POST['leave_group'])) {
                $this->groupModel->leaveGroup(intval($_POST['group_id']), $user_id);
            }
            if (isset($_POST['add_post'])) {
                $post_content = $_POST['post_content'];
                $image_id = null;

                if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                    $image_data = file_get_contents($_FILES['post_image']['tmp_name']);
                    $image_id = $this->filesModel->addImage($image_data);
                }

                $this->suggestedPostsModel->addPost($group_id, $user_id, $post_content, $image_id);
            }

            if (isset($_POST['delete_group'])) {
                $this->groupModel->deleteGroup(intval($_POST['group_id']), $user_id);
            }
        }

        $group = $this->groupModel->getGroupById($group_id, $user_id);
        $is_member = $group['is_member'] == 0 ? 0 : 1;
        $posts = $this->approvedPostsModel->getGroupPosts($group_id, $search);

        if (!$group) {
            header('Location: error.php?message=Group not found');
            exit();
        }

        include 'views/group.php';
    }
}
