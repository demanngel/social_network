<?php

namespace controllers;

require_once "./models/GroupModel.php";
require_once "./models/SuggestedPostsModel.php";
require_once "./models/ApprovedPostsModel.php";
require_once "./models/PostHistoryModel.php";
require_once "./controllers/HeaderController.php";
require_once "./models/UserModel.php";

use controllers\HeaderController;
use models\SuggestedPostsModel;
use models\ApprovedPostsModel;
use models\PostHistoryModel;
use models\GroupModel;
use models\UserModel;

class SuggestedPostsPageController
{
    private $groupModel;
    private $suggestedPostsModel;
    private $approvedPostsModel;
    private $postsHistoryModel;
    private $userModel;
    private $headerController;

    public function __construct($conn)
    {
        $this->groupModel = new GroupModel($conn);
        $this->userModel = new UserModel($conn);
        $this->suggestedPostsModel = new SuggestedPostsModel($conn);
        $this->approvedPostsModel = new ApprovedPostsModel($conn);
        $this->postsHistoryModel = new PostHistoryModel($conn);
        $this->headerController = new HeaderController($conn);
    }

    public function viewSuggestedPostsPage()
    {
        $this->headerController->viewHeader();

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'] ?? 'user';
        $group_id = intval($_GET['group_id']);

        if ($user_role == 'moderator') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $post_id = intval($_POST['post_id']);
                $user_post_id = intval($_POST['user_id']);
                $user_post_rating = $this->userModel->getRating($user_post_id);
                $image_id = intval($_POST['image_id']);
                $comment = $_POST['comment'] ?? null;
                $action = $_POST['action'];
                $approved_count = $_POST['approved_count'];
                $rejected_count = $_POST['rejected_count'];
                $on_moderation_count = $_POST['on_moderation_count'];

                if ($action === 'approve') {
                    $this->approvedPostsModel->addPost($post_id);
                    $this->postsHistoryModel->addPostHistory($post_id, 'approved', $user_id, $comment);
                    $this->suggestedPostsModel->updatePostStatus($post_id, 'approved');
                    $this->userModel->updateRating($user_post_rating + 0.1 * $approved_count, $user_post_id);
                } elseif ($action === 'revision') {
                    $this->postsHistoryModel->addPostHistory($post_id, 'revision', $user_id, $comment);
                    $this->suggestedPostsModel->updatePostStatus($post_id, 'on_revision');
                    $this->userModel->updateRating($user_post_rating - 0.1 * $on_moderation_count, $user_post_id);
                } elseif ($action === 'reject') {
                    $this->postsHistoryModel->addPostHistory($post_id, 'rejected', $user_id, $comment);
                    $this->suggestedPostsModel->updatePostStatus($post_id, 'rejected');
                    $this->userModel->updateRating($user_post_rating - 0.2 * $rejected_count, $user_post_id);
                }
            }
        }

        if ($user_role == 'user') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['send_to_moderation'])) {
                    $post_id = intval($_POST['post_id']);
                    $new_content = $_POST['content'];

                    $this->suggestedPostsModel->updatePost($post_id, $new_content);
                    $this->postsHistoryModel->addPostHistory($post_id, 'resent_to_moderation', $user_id);
                }
            }
        }

        $posts = $this->suggestedPostsModel->getPosts($group_id, $user_id, $user_role);

        include 'views/suggested_posts.php';
    }
}
