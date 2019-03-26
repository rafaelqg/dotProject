<?php
    $random = substr(md5(mt_rand()), 0, 7);
?>
<svg id="gantt-<?php echo $random ?>"></svg>
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
        var gantt = new Gantt("#gantt-<?php echo $random ?>", tasks(), {
            on_click: function (task) {
                var href = "<?php echo $this->taskClickURL ?>";
                href = href.replace('%id%', task.id);
                window.location.href = href;
            },
            on_date_change: function(task, start, end) {
                console.log(task, start, end);
                resetGantt();
            },
            on_progress_change: function(task, progress) {
                console.log(task, progress);
                resetGantt();
            },
            on_view_change: function(mode) {
                console.log(mode);
            }
        });
        
    })();
</script>