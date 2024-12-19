function initModal(modalId, openCallback = null) {
    const modal = document.getElementById(modalId);
    const span = modal.getElementsByClassName('close')[0];

    span.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }

    if (openCallback) {
        openCallback(modal);
    }
}

window.openEditModal = function(groupId, groupName, groupDescription) {
    const modal = document.getElementById('editModal');
    document.getElementById('edit_group_id').value = groupId;
    document.getElementById('edit_group_name').value = groupName;
    document.getElementById('edit_group_description').value = groupDescription;
    modal.style.display = 'block';
}

window.openEditPostModal = function(postId, postContent) {
    const modal = document.getElementById('editPostModal');
    document.getElementById('edit_post_id').value = postId;
    document.getElementById('edit_post_content').value = postContent;
    modal.style.display = 'block';
}

window.openEditUserModal = function(userId, username, email) {
    const modal = document.getElementById('editUserModal');
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    modal.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    const modals = ['editModal', 'editPostModal', 'editUserModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            initModal(modalId);
        }
    });
}); 