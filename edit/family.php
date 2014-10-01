<DOCTYPE !HTML>
<html>
  <head>
    <script src='/family/js/jquery.min.js'></script>
    <script src='/family/js/underscore-min.js'></script>
    <script src='/family/js/d3.min.js'></script>
    <script src='/family/js/typeahead.jquery.min.js'></script>
    <script src='/family/js/bloodhound.min.js'></script>
    <script src='/family/js/typeahead.bundle.min.js'></script>
    <script src='/family/js/bootstrap.min.js'></script>
    <script src='/family/js/tree.js'></script>
    <script src='/family/js/circletree.js'></script>
    <link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700' rel='stylesheet' type='text/css'>
    <link href="/family/style/reset.css" rel="stylesheet" type="text/css">
    <link href="/family/style/flaticon.css" rel="stylesheet" type="text/css">
    <link href="/family/style/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/family/style/style.css" rel="stylesheet" type="text/css">
  </head>
  <body>
    <header class='edit-header'>
      <div class='home-container'>
        <a class='home flaticon-home' href='http://ats.mit.edu/family/'></a>
      </div>
      <div class='header'>
        <h1>
          <?php
            require_once('../php/Tree.php');
            if (!empty($_GET)){
              $familyName = $_GET['family'];
              $tree = new Tree($familyName);
              echo $tree->getName();
            }
          ?>
        </h1>
        <input type='text' class='search typeahead' placeholder='search <?php echo $_GET['family']; ?>' />
      </div>
    </header>
    <div class='success-message'>Your changes have been saved</div>
    <div class='edit'>
    	<div class='header'>
	    	<h2>edit <? echo strtolower($tree -> getName()); ?></h2>
	    	<hr />
    	</div>
    	<div class='text'>
    		<p>Click on a name to add a little for that person.</p>
    		<p>If you would like to rearrange the tree, you can drag a node around and add it under any other node - this includes the node and its littles.</p>
    	  <p>It is advisable to make a few changes at a time and save frequently, as there maybe some unexpected bugs still.</p>
      </div>
    	<div class='form'>
    		<h3>Remove <em class='edit-person' id='remove-name'></em>?</h3>
    		<p>Note this will remove them AS WELL as all their littles</p>
    		<button id='remove-person'>Remove</button>
    		<h3>Add little for <em class='edit-person'></em></h3>
    		<form class='add-little'>
    			<div class='form-row'>
	    			<label>Name</label>
	    			<input type='text' name='name' />
	    		</div>
	    		<div class='form-row'>
	    			<label>Year</label>
	    			<input type='text' name='year' />
	    		</div>
	    		<div class='form-row'>
	    			<label>Email</label>
	    			<input type='text' name='email' />
	    		</div>
	    		<div class='form-row'>
	    			<label>Course</label>
	    			<input type='text' name='course' />
	    		</div>
	    		<div class='form-row'>
	    			<button id='add-little'>Add</button>
	    		</div>
	    	</form>
    	</div>
      <div class='save-button'>save</div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel">Saving Changes</h4>
          </div>
          <div class="modal-body">
            Are you sure you want to save the following <em class='num-changes'></em> changes?
            <ul>
              <li>No changes to be saved</li>
            </ul>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default btn-close" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-reset">Reset Tree</button>
            <button type="button" class="btn btn-success btn-save">Save changes</button>
          </div>
        </div>
      </div>
    </div>
    <script type='text/javascript'>
      function parseData(raw) {
        var family = null;
        var curLevel = -1;
        var current = null;
        _.each(raw, function(value, index) {
          var level = parseInt(value.level);
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
        return family;
      }
      $('.settings-container').click(function(e) {
        $(this).toggleClass('animate-left');
      });
      var familyName = window.location.pathname.split('/');
      familyName = familyName[familyName.length - 1];
      $.ajax({
        url: '/family/json/' + familyName,
        type: 'GET',
        success: function(data) {
          var family = parseData(JSON.parse(data));

          // constructs the suggestion engine
          var peeps = new Bloodhound({
              datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
              queryTokenizer: Bloodhound.tokenizers.whitespace,
              local: $.map(JSON.parse(data), function(peep) { return { value: peep.name }; })
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
          var viewerWidth = $(document).width() - $('.edit').width();
          var viewerHeight = $(document).height() - $('header').height();
          var tree = new Tree(family, viewerWidth, viewerHeight, true);
          tree.initialize();

          $('.tree').click(function(e) {
            $('svg').remove();
            tree.initialize();
            tree.update(tree.root);
            tree.centerVertical(tree.root);
          });

          $('.save-button').click(function(e) {
            var numChanges = tree.additions.length + tree.removals.length + tree.changed.length;
            if (numChanges > 0) {
              $('.modal-body ul').empty();
            }
            _.each(tree.additions, function(addition, index) {
              $('.modal-body ul').append("<li><em class='add'>Add</em> little " + addition.name + "</li>");
            });
            _.each(tree.removals, function(removal, index) {
              $('.modal-body ul').append("<li><em class='remove'>Remove</em> " + removal.name + " and his/her littles</li>");
            })
            _.each(tree.changed, function(change, index) {
              $('.modal-body ul').append("<li><em class='change'>Change</em> " + change.name + "'s big to " + change.big.name + "</li>");
            });
            $('.num-changes').text(numChanges);
            $('#myModal').modal('show');
          });

          $('.btn-reset').click(function(e) {
            $('svg').remove();
            family = parseData(JSON.parse(data));
            tree = new Tree(family, viewerWidth, viewerHeight, true);
            tree.initialize();
            $('#myModal').modal('hide');
          });

          $('.btn-save').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var allChanges = [];
            _.each(tree.additions, function(addition, index) {
              allChanges.push({
                changeType: 'addition',
                name: addition.name,
                email: addition.email,
                year: addition.year,
                big: {
                  name: addition.big.name,
                  email: addition.big.email,
                  course: addition.big.course,
                  year: addition.big.year,
                  id: addition.big.id
                }
              });
            });
            _.each(tree.removals, function(removal, index) {
              allChanges.push({
                changeType: 'removal',
                name: removal.name,
                email: removal.email,
                year: removal.year,
                id: removal.id,
                big: {
                  name: removal.big.name,
                  email: removal.big.email,
                  course: removal.big.course,
                  year: removal.big.year,
                  id: removal.big.id
                }
              });
            })
            _.each(tree.changed, function(change, index) {
              allChanges.push({
                changeType: 'change',
                name: change.name,
                id: change.id,
                email: change.email,
                year: change.year,
                big: {
                  name: change.big.name,
                  email: change.big.email,
                  course: change.big.course,
                  year: change.big.year,
                  id: change.big.id,
                }
              });
            });
            var changesMade = {'family': familyName, 'changes': allChanges};
            $.post('/family/edit/change', changesMade, function(data2) {
              if (JSON.parse(data2).length === 0 && changesMade.changes.length > 0) {
                $('.success-message').fadeIn();
                setTimeout(function() {
                  $('.success-message').fadeOut();
                }, 1500);
              }
              tree.removals = [];
              tree.additions = [];
              tree.changed = [];
              $('#myModal').modal('hide');
            });
          });
        }
      });
    </script>
  </body>
</html>