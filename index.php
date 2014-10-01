<!DOCTYPE HTML>
<html>
  <head>
    <title>ATS Family Tree</title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">  
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="name" content="noindex, nofollow">
    <link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700' rel='stylesheet' type='text/css'>
    <LINK href="style/reset.css" rel="stylesheet" type="text/css"> 
    <LINK href="style/search.css" rel="stylesheet" type="text/css">
    <LINK href="style/style.css" rel="stylesheet" type="text/css"> 
    <script src='js/jquery.min.js'></script>
    <script src='/family/js/typeahead.jquery.min.js'></script>
    <script src='/family/js/bloodhound.min.js'></script>
    <script src='/family/js/typeahead.bundle.min.js'></script>
  </head>
<body class='main'>
  <div class='background bg-jbapb'></div>
  <div class='background bg-greaterthanu'></div>
  <div class='background bg-bigfatfuzzylove'></div>
  <div class='background bg-atsgangstas'></div>
  <div class='background bg-funkybobasaurs'></div>
  <div class='search-container'>
    <div class='flaticon-search'></div>
    <input type='text' class='all-search' placeholder='FIND YOUR FAMILY'/>
  </div>
  <div class='center-container'>
    <a class='main-home' href='/'><img src='style/images/ATS_LOGO_VECTOR.png' /></a>
  </div>
  <div class='text-container'>
    <p class='family-name'></p>
  </div>
  <div class='main-container'>
    <a class='picture pirates' href='jbapb'><img src='style/images/boat.png' /></a>
    <a class='picture greaterthanu' href='greaterthanu'>>U</a>
    <a class='picture bigfatfuzzylove' href='bigfatfuzzylove'><img src='style/images/heart.png' /></a>
    <a class='picture atsgangstas' href='atsgangstas'>$</a>
    <a class='picture funkybobasaurs' href='funkybobasaurs'><img src='style/images/funkybobasaurs.png' /></a>
    <a class='picture bestatsfamily' href='bestatsfamily'>A</a>
    <a class='picture allstars' href='allstars'><img src='style/images/star.png' /></a>
  </div>
  <script type='text/javascript'>
    var map = {
      'jbapb': 'JONS BAD ASS PIRATES',
      'greaterthanu': 'GREATER THAN U',
      'bigfatfuzzylove': 'BIG FAT FUZZY LOVE',
      'atsgangstas': 'ATS GANGSTAS',
      'funkybobasaurs': 'FUNKY BOBASAURS',
      'bestatsfamily': "BEST ATS FAMILY",
      'allstars': 'ALL STARS'
    };
    $('.picture').hover(function(e) {
      $('.family-name').text(map[$(this).attr('href')]).css('opacity', 1);
      if ($(this).attr('href') !== 'bestatsfamily' || $(this).attr('href') !== 'allstars') {
        $('.main-home').css('opacity', 0);
      }
      $('.bg-' + $(this).attr('href')).css('opacity', 1);
    }, function(e) {
      $('.family-name').text('').css('opacity', 0);
      $('.background').css('opacity', 0);
      $('.main-home').css('opacity', 1);
    });
    $('.flaticon-search').click(function(e) {
      $('.search-container').toggleClass('animate-right');
      e.stopPropagation();
    });
    // $('body').click(function() {
    //   $('.search-container').removeClass('animate-right');
    // });

    $.ajax({
      url: 'nodes/atsgangstas', // Just need the family name doesn't do anything
      type: 'GET',
      success: function(data) {
        // constructs the suggestion engine
        var peeps = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            local: $.map(JSON.parse(data), function(peep) { return { value: peep.name, family: peep.family, id: peep.nodeID }; })
        });

        // kicks off the loading/processing of `local` and `prefetch`
        peeps.initialize();
        $('.all-search').typeahead({
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

        $('.all-search').bind('typeahead:selected', function(obj, datum, name) {
          window.location='http://ats.mit.edu/family/' + datum.family + '/' + datum.id;
          // _.each(nodes, function(d) { d.focus = false; });
          // var node = _.find(nodes, function(d) { return d.name.toLowerCase() === datum.value.toLowerCase(); });
          // node.focus = !_this.edit;
          // _this.update(node);
          // _this.centerNode(node);
        });
      }
    })
  </script>
</body>
</html>