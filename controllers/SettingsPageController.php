<?php

namespace controllers;

require_once "./models/SubscribersModel.php";
require_once "./models/GroupModel.php";
require_once "./controllers/HeaderController.php";

use models\SubscribersModel;
use models\GroupModel;
use controllers\HeaderController;

class SettingsPageController {
    private $subscribersModel;
    private $groupModel;
    private $headerController;

    public function __construct($conn) {
        $this->subscribersModel = new SubscribersModel($conn);
        $this->groupModel = new GroupModel($conn);
        $this->headerController = new HeaderController($conn);
    }

    public function viewSubscribersPage() {
        $this->headerController->viewHeader();

        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'] ?? 'user';
        $search_term = $_GET['search'] ?? '';
        $search = '%' . $search_term . '%';
        $group_id = intval($_GET['group_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['remove_user'])) {
                $user_id_to_remove = intval($_POST['user_id']);
                $this->subscribersModel->deleteSubscriber($group_id, $user_id_to_remove);
            }
        }

        $group = $this->groupModel->getGroupById($group_id, $user_id);
        $group_name = $group['name'] ?? '';

        $subscribers = $this->subscribersModel->getSubscribers($group_id, $search);

        include 'views/subscribers.php';
    }
}
