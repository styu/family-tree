<DOCTYPE !HTML>
<html>
  <head>
    <script src='/family/js/jquery.min.js'></script>
    <script src='http://underscorejs.org/underscore-min.js'></script>
    <script src='/family/js/d3.min.js'></script>
    <script src='/family/js/typeahead.jquery.min.js'></script>
    <script src='/family/js/bloodhound.min.js'></script>
    <script src='/family/js/typeahead.bundle.min.js'></script>
    <script src='/family/js/tree.js'></script>
    <script src='/family/js/circletree.js'></script>
    <link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700' rel='stylesheet' type='text/css'>
    <link href="/family/style/reset.css" rel="stylesheet" type="text/css">
    <link href="/family/style/flaticon.css" rel="stylesheet" type="text/css">
    <link href="/family/style/style.css" rel="stylesheet" type="text/css">
  </head>
  <body>
    <header>
      <div class='home-container'>
        <a class='home flaticon-home' href='/family/'></a>
      </div>
      <div class='header'>
        <h1>
          <?php
            require_once('php/Tree.php');
            if (!empty($_GET)){
              $familyName = $_GET['family'];
              $tree = new Tree($familyName);
              echo $tree->getName();
            }
          ?>
        </h1>
        <input type='text' class='search typeahead' placeholder='search <?php echo $_GET['family']; ?>' />
      </div>
      <div class='settings-container'>
        <div class='settings flaticon-tools'></div>
        <div class='the-rest'>
          <div class='icon tree flaticon-network'></div>
          <!-- <div class='icon circular flaticon-circular'></div> -->
          <a class='icon edit-icon flaticon-edit' href='/family/edit/<?php echo $_GET['family']; ?>'></a>
        </div>
      </div>
    </header>
    <script type='text/javascript'>
      $('.settings-container').click(function(e) {
        $(this).toggleClass('animate-left');
      });
      var family = window.location.pathname.split('/');
      hasFocus = family.length > 3;
      focusNode = null;
      if (hasFocus) {
        focusNode = family[3];
      }
      family = family[2];
      console.log(hasFocus, focusNode);
      $.ajax({
        url: '/family/json/' + family,
        type: 'GET',
        success: function(data) {
          var raw = JSON.parse(data);
          var family = null;
          var curLevel = -1;
          var current = null;
          _.each(raw, function(value, index) {
            var level = parseInt(value.level);
            if (hasFocus && value.id === focusNode) {
              value.focus = true;
              focusNode = value;
            }
            if (level > curLevel) {
              value.big = current;
              if (family == null) {
                family = value;
              } else {
                if (current.children) {
                  current.children.push(value);
                } else {
                  current.children = [value];
                }
              }
            } else if (level < curLevel) {
              big = current;
              for (var i = level; i <= curLevel; ++i) {
                big = big.big;
              }
              value.big = big;
              if (value.big.children) {
                value.big.children.push(value);
              } else {
                value.big.children = [value];
              }
            } else {
              value.big = current.big;
              current.big.children.push(value);
            }
            current = value;
            curLevel = level;
          });

          // constructs the suggestion engine
          var peeps = new Bloodhound({
              datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
              queryTokenizer: Bloodhound.tokenizers.whitespace,
              // `states` is an array of state names defined in "The Basics"
              local: $.map(raw, function(peep) { return { value: peep.name }; })
          });

          // kicks off the loading/processing of `local` and `prefetch`
          peeps.initialize();
          $('.search').typeahead({
              hint: false,
              highlight: true,
              minLength: 1
              },
              {
              name: 'peeps',
              displayKey: 'value',
              // `ttAdapter` wraps the suggestion engine in an adapter that
              // is compatible with the typeahead jQuery plugin
              source: peeps.ttAdapter()
          });

          // size of the diagram
          var viewerWidth = $(document).width();
          var viewerHeight = $(document).height() - $('header').height();
          var tree = new Tree(family, viewerWidth, viewerHeight, false);
          tree.initialize(hasFocus, focusNode);

          $('.tree').click(function(e) {
            $('svg').remove();
            tree.initialize();
            tree.update(tree.root);
            tree.centerVertical(tree.root);
          });

          // $('.circular').click(function(e) {
          //   $('svg').remove();
          //   circleTree.initialize();
          //   circleTree.update();
          // })
        }
      });
    </script>
  </body>
</html>