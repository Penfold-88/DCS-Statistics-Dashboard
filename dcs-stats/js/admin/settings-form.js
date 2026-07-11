    const settingsFormConfig = window.DCS_ADMIN_SETTINGS_FORM_CONFIG || {};
    const dependencies = settingsFormConfig.dependencies || {};
    const settingsText = settingsFormConfig.i18n || {};

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.group-header').forEach(function(header) {
            header.addEventListener('click', function() {
                const groupName = this.dataset.group;
                const content = document.getElementById('group_' + groupName);

                this.classList.toggle('collapsed');
                content.classList.toggle('collapsed');
            });
        });

        const bulkActions = document.querySelector('.bulk-actions');
        if (bulkActions) {
            const expandAllBtn = document.createElement('button');
            expandAllBtn.type = 'button';
            expandAllBtn.className = 'btn btn-secondary';
            expandAllBtn.textContent = settingsText.expandAll;
            expandAllBtn.onclick = function() { toggleAllGroups(false); };
            bulkActions.appendChild(expandAllBtn);

            const collapseAllBtn = document.createElement('button');
            collapseAllBtn.type = 'button';
            collapseAllBtn.className = 'btn btn-secondary';
            collapseAllBtn.textContent = settingsText.collapseAll;
            collapseAllBtn.onclick = function() { toggleAllGroups(true); };
            bulkActions.appendChild(collapseAllBtn);
        }
    });

    function toggleAllGroups(collapse) {
        document.querySelectorAll('.group-header').forEach(function(header) {
            const groupName = header.dataset.group;
            const content = document.getElementById('group_' + groupName);

            if (collapse) {
                header.classList.add('collapsed');
                content.classList.add('collapsed');
            } else {
                header.classList.remove('collapsed');
                content.classList.remove('collapsed');
            }
        });
    }

    function toggleAll(enable) {
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (checkbox.closest('[data-locked="true"]')) {
                checkbox.checked = false;
                checkbox.disabled = true;
                return;
            }
            checkbox.checked = enable;
            checkbox.disabled = false;
        });

        if (!enable) {
            updateDependencies();
        }
    }

    function toggleGroup(groupName, enable) {
        toggleAll(false);

        if (!enable) {
            document.querySelectorAll('[id^="feature_nav_home"], [id^="feature_nav_leaderboard"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        }
    }

    function updateDependencies() {
        for (const [parent, children] of Object.entries(dependencies)) {
            const parentCheckbox = document.getElementById('feature_' + parent);
            if (parentCheckbox) {
                const isEnabled = parentCheckbox.checked;

                children.forEach(child => {
                    const childElement = document.querySelector(`[data-feature="${child}"]`);
                    const childCheckbox = document.getElementById('feature_' + child);

                    if (childElement && childCheckbox) {
                        if (childElement.dataset.locked === 'true') {
                            childCheckbox.checked = false;
                            childCheckbox.disabled = true;
                            childElement.classList.add('disabled');
                            return;
                        }
                        if (!isEnabled) {
                            childCheckbox.checked = false;
                            childCheckbox.disabled = true;
                            childElement.classList.add('disabled');
                        } else {
                            childCheckbox.disabled = false;
                            childElement.classList.remove('disabled');
                        }
                    }
                });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        for (const parent of Object.keys(dependencies)) {
            const checkbox = document.getElementById('feature_' + parent);
            if (checkbox) {
                checkbox.addEventListener('change', updateDependencies);
            }
        }
    });

    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        const majorFeatures = ['nav_home', 'credits_enabled', 'squadrons_enabled'];
        const disabledMajor = [];

        majorFeatures.forEach(feature => {
            const checkbox = document.getElementById('feature_' + feature);
            if (checkbox && !checkbox.checked) {
                disabledMajor.push(feature);
            }
        });

        if (disabledMajor.length > 0) {
            if (!confirm(settingsText.majorConfirm)) {
                e.preventDefault();
            }
        }
    });
