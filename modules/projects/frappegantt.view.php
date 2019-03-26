<svg id="gantt"></svg>
<script>
    var tasks = <?php echo $json ?>;
    var gantt = new Gantt("#gantt", tasks, {
        on_click: function (task) {
            var href = "<?php echo $this->taskClickURL ?>";
            href = href.replace('%id%', task.id);
            window.location.href = href;
        }
    });
    
</script>