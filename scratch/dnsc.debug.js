const dnsc = require('dns');
const net = require('net');
var curConnId = -1;
var verbose = 11;//  >10: print all traffic.
var tld = '.t.rma3.com';
var kueUp = []; //queue of things to send over dns to the server.
var kueDown = []; //queue of things to send to the client.
var pollDelay = 70; //how often to poll for new data.
var pollSlowDelay = 250; //when very little data is coming down, this will be used instead. NOTE: Adjust the way the poll delay is being handled.
//This is with the assumption that loads are downstream heavy.
//further analysis required of this.
var curPollDelay = pollDelay; //the delay in use at this time. Changes
//based on activity.
//TODO: Figure out why data is garbled when sent over relaying DNS.
//TODO: Implement Poll Delay adjusting. Right now is just pollDelay.
var payloadsize = 32;
var writeSocket = null;
var LOCAL_PORT = 1080;
process.on('uncaughtException', function (err) {
  console.error(err.stack);
  console.log("Node NOT Exiting...");
});
//set the DNS server.
//dnsc.setServers(['35.165.209.211']);
//dnsc.setServers(['8.8.8.8']);

//empty handler for writing.
function dnsNullHandler(err, res)
{
  //if( res != undefined )
  {
    //console.log("null handler: result is " + res );
  }
  
}
//handler for reading dns data from the server.
//whatever should be done with incoming data should be handled here.
function dnsReadHandler(err, res)
{
  if( res != null && res[0] != null )
  {
    var b = Buffer.from( res[0], 'base64' )
    if( writeSocket != null )
    {
      writeSocket.write( b );
      //curPollDelay = pollDelay;
    }
    else
    {
    }
    if(verbose > 10) console.log(b.toString());
  }
  else
  {
  }
}
//write data to server over DNS. 
//data will be passed into buffer from.
function dnsWriter(data)
{
  var bx = Buffer.from( data ).toString('base64');
  dnsc.resolveCname( 'w.' + curConnId + '.' + bx + tld, dnsNullHandler);
}
//poll function for attempting to read data from the server.
function poll()
{
  dnsc.resolveCname( 'r.' + curConnId.toString()
    + tld , dnsReadHandler);
  //this is scheduled in the read handler now.
  //setTimeout( poll, pollDelay );
  if( kueUp.length > 0 && curConnId != -1)
  {
    dnsWriter( kueUp.shift() );
    curPollDelay = pollDelay;
  }

  setTimeout( poll, curPollDelay );
  //curPollDelay = pollSlowDelay;
}

function startConnection()
{
  //create a connection.
  dnsc.resolveCname( 'gid' + tld , function(err, res){
    if( res != null && res[0] != null )
    {
      var b = Buffer.from( res[0], 'base64' )
      if( !isNaN( b ) )
      { 
        curConnId = parseInt( b );//store the current server connection id
        //dnsWriter("GET / HTTP/1.1\nHost: rabbit.eng.miami.edu\n\n");
        setTimeout( poll, pollDelay);
      }
      if( verbose > 10 )console.log(b.toString());
    }
  });
}

//Server to accept local connections and convert them to DNS
var server = net.createServer(function (socket)
{
});

server.on('connection', function( socket )
{
  //on connection start a connection.
  startConnection();

  writeSocket = socket;

  socket.on('data', function( msg )
  {
    if( verbose > 10) console.log(" getting client data ");
    for( var i = 0; i < msg.length; i += payloadsize )
    {
      var b2 = Buffer.from( msg.slice( i, i+payloadsize ) );
      if( verbose > 10 ) console.log( b2.toString() );
      kueUp.push( b2 );
    }
  });
});//server.on connection

server.listen( LOCAL_PORT );
