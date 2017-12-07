const dnsc = require('dns');
var curConnId = -1;
dnsc.setServers(['127.0.0.1']);

function dnsNullHandler(err, res)
{
  console.log("null handler: result is " + res );
}
function dnsReadHandler(err, res)
{
  if( res != null && res[0] != null )
  {
    var b = Buffer.from( res[0], 'base64' )
    console.log(b.toString());
  }
}
function poll()
{
  dnsc.resolveCname( 'r.' + curConnId.toString()
    + '.test.ai' , dnsReadHandler);
  setTimeout( poll, 100 );
}

//create a connection.
dnsc.resolveCname( 'gid.test.ai' , function(err, res){
  if( res != null && res[0] != null )
  {
    var b = Buffer.from( res[0], 'base64' )
    if( !isNaN( b ) )
    { 
      curConnId = parseInt( b );//store the current server ocnnection id
      var bx = Buffer.from('GET / HTTP/1.1\n\n').toString('base64');
      dnsc.resolveCname( 'w.' + curConnId
        + '.' + bx + '.test.ai' , dnsNullHandler);
      setTimeout( poll, 100);
    }
    console.log(b.toString());
  }
});


