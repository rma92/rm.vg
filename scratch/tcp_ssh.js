var net = require('net');
//based on https://gonzalo123.com/2012/09/17/building-a-simple-tcp-proxy-server-with-node-js/
//a modification was required so that a connection to the remote server is 
//initialized as soon as a connection to the proxy is, rather than waiting for
//data before starting the connection.  SSH clients do not send data, and the
//server is expected to send data on connection.

var LOCAL_PORT = 1080;
//var REMOTE_PORT = 22; var REMOTE_ADDR = "10.0.0.1";
var REMOTE_PORT = 80;var REMOTE_ADDR = "129.171.33.6";

var server = net.createServer(function (socket)
{
});

server.on('connection', function( socket ){
  console.log('#'); 
  var serviceSocket = new net.Socket();
  serviceSocket.connect(parseInt(REMOTE_PORT), REMOTE_ADDR, function() {
    //console.log('>>FROM proxy 2 remote', "");
    serviceSocket.write("");
  });
    
  serviceSocket.on("data", function (data) {
    //console.log('<< From remote to proxy', data.toString());
    socket.write(data);
    //console.log('>> From proxy to client', data.toString());
  });
  socket.on('data', function( msg )
  {
    //console.log('**START**');
    //console.log('<<From client 2 proxy ', msg.toString());
    serviceSocket.write( msg );
  });
});

server.listen( LOCAL_PORT );
console.log('TCP server on port: ' + LOCAL_PORT );
