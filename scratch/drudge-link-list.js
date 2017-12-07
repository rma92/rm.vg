var http = require('http');
var cheerio = require('cheerio');
var options = {
  host: 'drudgereport.com',
  port: '80',
  path: '/'
};

var callback = function(response){
  var body = '';
  response.on('data', function(data)
  {
    body += data;
  });
  response.on('end', function(){
    var parsedHTML = cheerio.load(body);
    parsedHTML('a').map( function(i, link) {
      var href= cheerio(link).attr('href');
      var text = cheerio(link).text();
      console.log(text);
    });
    //console.log(body);
  });
}

var req = http.request(options, callback);
req.end();
