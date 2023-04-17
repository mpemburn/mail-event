jQuery(document).ready(function ($) {

    class MailEvent {

        constructor() {
            this.addListeners();
        }

        addListeners() {
            $('#mail_event_update').on('click', function () {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "/wp-admin/admin-ajax.php",
                    data: {
                        action: 'save_mail_event_settings',
                        template_id: $('input[name="mail_event_template_id"]').val(),
                        from_email: $('input[name="mail_event_from_email"]').val(),
                        from_name: $('input[name="mail_event_from_name"]').val()
                    },
                    success: function (data) {
                        location.reload();
                    },
                    error: function (msg) {
                        console.log(msg);
                    }
                });
            })

            $('button[data-email]').on('click', function () {
                let email = $(this).data('email');
                let buttonAction = (email) ? 'remove' : 'add';
                let data = {
                    action: 'add_or_remove_email',
                    button_action: buttonAction
                }

                if (buttonAction === 'add') {
                    data.first_name = $('input[name="new_first_name"]').val();
                    data.last_name = $('input[name="new_last_name"]').val();
                    data.email = $('input[name="new_email"]').val();
                }

                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "/wp-admin/admin-ajax.php",
                    data: data,
                    success: function (data) {
                        console.log(data);
                        //location.reload();
                    },
                    error: function (msg) {
                        console.log(msg);
                    }
                });

            })
        }
    }

    new MailEvent();
});
