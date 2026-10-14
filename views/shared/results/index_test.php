<!DOCTYPE HTML>
<html lang="en">
<head>
    <title>Testing JQuery UI </title>
    <style>
        *{
            font-family: Arial, Verdana, sans-serif;
        }

        #facets{
            width: 20%;
            display: inline-block;
            vertical-align: top;
            padding: 0 10px 0 0;
        }

        #results{
            width: 60%;
            display: inline-block;
        }

        .result{
            width: 90%;
            height: 150px;
            display: flex;
            align-items: center;
            padding:15px;
            border-radius: 7px;
            /* border: 1px solid black; */
            margin: 0 0 15px 0;
            background-color: #eee;
            box-shadow: 5px 5px 5px #666666;
        }

        .something{
            background-color: #41e78a;
        }

        .something>ul{
            padding: 0 0 5px 0;
            margin-top: 5px;
        }

        .something>ul>li{
            padding: 4px 0 4px 20px;
        }

        .invisible{
            display: none;
        }

        .something>ul>li:hover{
            background-color: #06a04b;
        }

        .facet-label{
            font-weight: bold;
            color: white;
            background-color: #2945a1;
            padding: 5px;
            margin-top: 5px;
        }

        .count{
            float: right;
            padding: 1px 0 0 0;
            text-align: center;
            width: 20px;
            height: 17px;
            background-color: black;
        }
    </style>
    <script
            src="https://code.jquery.com/jquery-3.6.0.js"
            integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
            crossorigin="anonymous">
    </script>
    <script
            src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"
            integrity="sha256-DI6NdAhhFRnO2k51mumYeDShet3I8AKCQf/tf7ARNhI="
            crossorigin="anonymous">
    </script>
    <?php
        $facet_group = array(
            /*
            "Group 1" => array(1, 2, 3, 4),
            "Group 2" => array(1, 2),
            "Group 3" => array(1, 2, 3, 4, 5, 6, 7),
            "Group 4" => array(1, 2, 3)*/
            array('facet-name' => 'group 1', 'facet-group' => array(1, 2)),
            array('facet-name' => 'group 2', 'facet-group' => array(1, 2, 3, 4, 5, 6)),
            array('facet-name' => 'group 3', 'facet-group' => array(1, 2,)),
            array('facet-name' => 'group 2', 'facet-group' => array(1, 2, 3, 4)),
            array('facet-name' => 'group 3', 'facet-group' => array(1)),
            array('facet-name' => 'group 3', 'facet-group' => array(1, 2, 3))
        );

        $results = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j');
    ?>
</head>
<body>
<div id="facets">
    <?php foreach($facet_group as $group):?>
    <div class="something">
        <div class="facet-label">
            <?php echo $group['facet-name']?> <span class="count"><?php echo sizeof($group['facet-group'])?></span>
        </div>
        <ul class="invisible">
            <?php foreach($group['facet-group'] as $item):?>
                <li><?php echo $item?></li>
            <?php endforeach;?>
        </ul>
    </div>
    <?php endforeach;?>
</div>
<div id="results">
    <?php foreach($results as $result):?>
    <div class="result">
        <span><?php echo $result;?></span>
    </div>
    <?php endforeach;?>
</div>
<script>
    $('.count').on('click', function(){
        //alert($(this).text());
        $(this).parent().next().slideToggle(200);
    });
</script>
</body>
</html>
