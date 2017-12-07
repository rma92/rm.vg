var http = require('http');
var cheerio = require('cheerio');
var url = require('url');
var dnsd = require('dnsd');
var options = {
  host: 'drudgereport.com',
  port: '80',
  path: '/'
};

var linkText = [];
var linkUrl = [];
var pages = [];
var output = "";
var callback = function(response){
  var body = '';
  response.on('data', function(data)
  {
    body += data;
  });
  response.on('end', function(){
    //var parsedHTML = cheerio.load(body);
    var p2 = body.split("<!");
    for(var i = 0; i < p2.length; ++i )
    {
      //console.log(p2[i]);
      var p2bracketIndex = p2[i].indexOf(">");
      var p2a = p2[i].substring(0, p2bracketIndex);
      if( p2a[0] == ' ' && p2a.trim()[0] != 'L')
      {
        pages.push([]);
        console.log("$$$$$" + p2a.trim() + "$$$$$");
        var parsedHTML = cheerio.load(p2[i]);
        parsedHTML('a').map( function(i, link) {
          var href= cheerio(link).attr('href');
          var text = cheerio(link).text();
          pages[pages.length-1].push( linkText.length);
//          (pages[i]).push( linkText.length);
          linkText.push(text);
          linkUrl.push(href);
        });
      }
    }
    /*
    for(var i = 0; i < linkText.length; ++i )
    {
      output += linkText[i] + "\n";
    }
    console.log(output)
    */
  });//on end function
}

var req = http.request(options, callback);
req.end();

http.createServer( 
  function( request, response){
    var pathname = url.parse(request.url).pathname;
    console.log("Request for " + pathname + " received.");
    if( pathname == "/")
    {
      response.writeHead(200, {'Content-Type': 'text/html'});
      response.write("<html><body><h1>DrudgeReport</h1>");
      /*for(var i = 0; i < pages.length; ++i )
      {
        for( var j = 0; j < pages[i].length; ++j )
        {
          response.write( linkText[pages[i][j]] );
        }
        response.write("<br/>");
      }*/
      for( var j = 0; j < pages[0].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[0][j]] 
          + "'>" + linkText[pages[0][j]] + "</a><br/>");
      }
      for( var j = 0; j < pages[1].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[1][j]] 
          + "'>" + linkText[pages[1][j]] + "</a><br/>");
      }
      response.write("<a href=/1>1</a><br/><a href=/2>2</a><br/><a href=/3>3</a><br/>");
      response.write("</body></html>");
    }
    else if( pathname == "/1")
    {
      response.write("<html><body><h1>DrudgeReport1</h1>");
      for( var j = 0; j < pages[2].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[2][j]] 
          + "'>" + linkText[pages[2][j]] + "</a><br/>");
      }
      response.write("</body></html>");
    }
    else if( pathname == "/2")
    {
      response.write("<html><body><h1>DrudgeReport2</h1>");
      for( var j = 0; j < pages[3].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[3][j]] 
          + "'>" + linkText[pages[3][j]] + "</a><br/>");
      }
      response.write("</body></html>");
    }
    else if( pathname == "/3")
    {
      response.write("<html><body><h1>DrudgeReport1</h1>");
      for( var j = 0; j < pages[4].length; ++j )
      {
        response.write( "<a href='" 
          + linkUrl[pages[4][j]] 
          + "'>" + linkText[pages[4][j]] + "</a><br/>");
      }
      response.write("</body></html>");
    }
    else
    {
    response.writeHead(404, {'Content-Type': 'text/html'});
    }
    response.end();
}).listen(8080);

dnsd.createServer( function(req,res){
  var question = res.question[0]
    , hostname = question.name
    , length = hostname.length
    , ttl = Math.floor(Math.random() * 3600)

  if( question.type == 'A')
  {
    res.end('1.2.3.4');
  }
  else
  {
    var questionSplit = question.name.split(".");
    var retData = "";
    if( questionSplit[0] == 'd0')
    {
      for( var j = 0; j < pages[0].length; ++j )
      {
        retData += linkText[pages[0][j]] + ",";
      }
      for( var j = 0; j < pages[1].length; ++j )
      {
        retData += linkText[pages[1][j]] + ",";
      }
    }
    else if( questionSplit[0] == 'd1' )
    {
      for( var j = 0; j < pages[2].length; ++j )
      {
        retData += linkText[pages[2][j]] + ",";
      }
    }
    else if( questionSplit[0] == 'd2' )
    {
      for( var j = 0; j < pages[3].length; ++j )
      {
        retData += linkText[pages[3][j]] + ",";
      }
    }
    else if( questionSplit[0] == 'd3' )
    {
      for( var j = 0; j < pages[4].length; ++j )
      {
        retData += linkText[pages[4][j]] + ",";
      }
    }
    console.log(retData.length);
    res.answer.push({name:hostname, type:'TXT', 
      data:retData,
      'ttl':ttl});
    res.end();
  }
}).listen(53, '127.0.0.1');
console.log("Server http://127.0.0.1:8080");
console.log("DNS Server 127.0.0.1:53");
