<?php

namespace controllers;

require_once "./models/SubscribersModel.php";
require_once "./models/GroupModel.php";
require_once "./controllers/HeaderController.php";

use models\PostHistoryModel;
use controllers\HeaderController;

class PostHistoryPageController {
    private $postHistoryModel;
    private $headerController;

    public function __construct($conn) {
        $this->postHistoryModel = new PostHistoryModel($conn);
        $this->headerController = new HeaderController($conn);
    }

    public function viewPostHistoryPage() {
        $this->headerController->viewHeader();

        $post_id = $_GET['post_id'];

        $post_history = $this->postHistoryModel->getPostsHistory($post_id);

        include 'views/post_history.php';
    }
}
