var net = require('net');
//based on https://gonzalo123.com/2012/09/17/building-a-simple-tcp-proxy-server-with-node-js/
var LOCAL_PORT = 1080;
var REMOTE_PORT = 80;
var REMOTE_ADDR = "129.171.33.6";

var server = net.createServer(function (socket)
{
  socket.on('data', function( msg )
  {
    console.log('**START**');
    console.log('<<From client 2 proxy ', msg.toString());
    var serviceSocket = new net.Socket();
    serviceSocket.connect(parseInt(REMOTE_PORT), REMOTE_ADDR, function() {
      console.log('>>FROM proxy 2 remote', msg.toString());
      serviceSocket.write(msg);
    });
    serviceSocket.on("data", function (data) {
      console.log('<< From remote to proxy', data.toString());
      socket.write(data);
      console.log('>> From proxy to client', data.toString());
    });
  });
});

server.listen( LOCAL_PORT );
console.log('TCP server on port: ' + LOCAL_PORT );
