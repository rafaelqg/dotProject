<?php
    $random = substr(md5(mt_rand()), 0, 7);
?>
<svg id="gantt-<?php echo $random ?>"></svg>
<p class="gantt-controls" id="gantt-controls-<?php echo $random ?>">
    <button data-view="Quarter Day"><?php echo $AppUI->_("Quarter Day"); ?></button>
    <button data-view="Half Day"><?php echo $AppUI->_("Half Day"); ?></button>
    <button data-view="Day"><?php echo $AppUI->_("Day"); ?></button>
    <button data-view="Week"><?php echo $AppUI->_("Week"); ?></button>
    <button data-view="Month"><?php echo $AppUI->_("Month"); ?></button>
    <button data-view="Year"><?php echo $AppUI->_("Year"); ?></button>
</p>
<script>
    (function(){
        var tasks = function() {
            return <?php echo $json ?>;
        }
        var resetGantt = function() {
            setTimeout(() => {
                gantt.clear();
                gantt.setup_tasks(tasks());
                gantt.render();
            }, 100);
        }
        var id = "<?php echo $random ?>";
        var controls = document.getElementById('gantt-controls-'+id);
        var buttons = controls.getElementsByTagName('BUTTON');
        var gantt = new Gantt("#gantt-<?php echo $random ?>", tasks(), {
            on_click: function (task) {
                var href = "<?php echo $this->taskClickURL ?>";
                href = href.replace('%id%', task.id);
                window.location.href = href;
            },
            on_date_change: function(task, start, end) {
                resetGantt();
            },
            on_progress_change: function(task, progress) {
                resetGantt();
            },
            on_view_change: function(mode) {
                for (var buttonID = 0; buttonID < buttons.length; buttonID++) {
                    var button = buttons[buttonID];
                    var active = mode == button.getAttribute('data-view');
                    button.disabled = active;
                    button.className = active ? "active" : "button";
                }
            }
        });
        for (var buttonID = 0; buttonID < buttons.length; buttonID++) {
            buttons[buttonID].onclick = function(e) {
                var button = e.target;
                gantt.change_view_mode(button.getAttribute('data-view'));
            }
        }
    })();
</script>
<style>
    .gantt-controls .active {
        color: black;
        background: none;
        border: none;
        font-weight: bold;
    }
</style>
