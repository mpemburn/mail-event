jQuery(document).ready(function ($) {

    class MailEvent {

        constructor() {
            this.enable;
            this.emailRegex = '^(([^<>()[\\]\\\\.,;:\\s@"]+(\\.[^<>()[\\]\\\\.,;:\\s@"]+)*)|.(".+"))@((\\[[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\])|(([a-zA-Z\\-0-9]+\\.)+[a-zA-Z]{2,}))$';
            this.emails;
            this.existingAddresses = [];
            this.getExistingAddresses();
            this.addListeners();
        }

        getExistingAddresses() {
            let self = this;

            this.emails = $('td.email');
            this.emails.each(function () {
                self.existingAddresses.push($(this).html());
            });
        }

        addListeners() {
            let self = this;
            $('#mail_event_update').on('click', function () {
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "/wp-admin/admin-ajax.php",
                    data: {
                        action: 'save_mail_event_settings',
                        template_id: $('input[name="mail_event_template_id"]').val(),
                        from_email: $('input[name="mail_event_from_email"]').val(),
                        from_name: $('input[name="mail_event_from_name"]').val(),
                        list_id: $('select[name="mail_event_list_id"]').val()
                    },
                    success: function (data) {
                        location.reload();
                    },
                    error: function (msg) {
                        console.log(msg);
                    }
                });
            })

            // Test that new name and email are entered correctly
            $('input[name^="new_"]').on('keyup', function () {
                let newEmail = $('input[name="new_email"]').val();
                self.enable = true;
                $('input[name^="new_"]').each(function () {
                    if ($(this).val() === '') {
                        self.enable = false;
                    }
                });

                if (! newEmail.toLowerCase().match(self.emailRegex)) {
                    self.enable = false;
                }

                if (self.existingAddresses.includes(newEmail)) {
                    alert('"' + newEmail + '" is already in this list.');
                    self.enable = false;
                }

                $('button.add-button').prop('disabled', ! self.enable);
            });

            // Add or remove names
            $('button[data-id], button.add-button').on('click', function () {
                let email = $(this).data('email');
                let contactId = $(this).data('id');
                let buttonAction = (contactId) ? 'remove' : 'add';
                let data = {
                    action: 'add_or_remove_email',
                    button_action: buttonAction
                }

                if (buttonAction === 'remove') {
                    data.contact_id = contactId;
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
                        location.reload();
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
