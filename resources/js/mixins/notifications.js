export const notifications = {
    methods: {
        showError: function (value) {
            if (typeof value === 'object' && value !== null) {
                if (value.status == 422 && value.data && value.data.errors) {
                    const first = Object.keys(value.data.errors)[0];
                    value = value.data.errors[first][0];
                } else if (value.data && value.data.message) {
                    value = value.data.message;
                } else if (value.message) {
                    value = value.message;
                } else {
                    value = 'An error has occurred';
                }
            }
            if (typeof value === 'undefined' || !value) {
                value = 'An error has occurred';
            }
            this.$notify({
                message: String(value),
                type: 'error',
                top: false,
                bottom: true,
                left: false,
                right: true,
                showClose: true,
                closeDelay: 6000,
            });
        },
        showSuccess: function (value = 'success') {
            this.$notify({
                message: value,
                type: 'success',
                top: false,
                bottom: true,
                left: false,
                right: true,
                showClose: true,
                closeDelay: 4000,
            });
        },
        showAlert(value) {
            this.$notify({
                message: value,
                type: 'warning',
                top: false,
                bottom: true,
                left: false,
                right: true,
                showClose: true,
                closeDelay: 6000,
            });
        },

        showAlert1() {
            this.$notify({
                message: "you must configure your TMDB ( to able to fetch movies info and langs ) api key in settings",
                type: 'warning',
                top: false,
                bottom: true,
                left: false,
                right: true,
                showClose: true,
                closeDelay: 6000,
            });
        },
        showConfirm: function (value) {
            Vue.dialog
                .confirm('Please confirm to continue')
                .then(function (dialog) {
                    value();
                })
                .catch(function () {
                });
        },

        showConfirmBan: function (value) {
            Vue.dialog
                .confirm('Are you sure to ban this user ?')
                .then(function (dialog) {
                    value();
                })
                .catch(function () {
                });
        },

        showConfirmUnBan: function (value) {
            Vue.dialog
                .confirm('Are you sure to unban this user ?')
                .then(function (dialog) {
                    value();
                })
                .catch(function () {
                });
        },

    },
};
