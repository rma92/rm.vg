using System;
using System.Net;
using System.Net.Sockets;
using System.Text;
using System.Threading;

//A Basic Threaded Web Server (that doesn't actually serve anything but make 404 errors). to use, create RMWebServer( 80 ); or whatever port you want.
public class RMWebServer
{
  private TcpListener myListener;
  private Thread listenThread;
  private int port;

  public RMWebServer(int myPort)
  {
    port = myPort;
    try
    {
      myListener = new TcpListener(IPAddress.Any, port);
      
      listenThread = new Thread(new ThreadStart(ClientListener));
      listenThread.Start();
      
    }
    catch(Exception ex)
    {
      Console.WriteLine("Exception on start listen: " + ex.ToString());
    }
  }

  public void ClientListener()
  {
    myListener.Start();
    Console.WriteLine("Running on *:" + port + "...\n");
    while(true)
    {
      TcpClient client = myListener.AcceptTcpClient();
      Thread clientThread = new Thread( new ParameterizedThreadStart( HandleClientComm));
      clientThread.Start(client);
    }
  }

  public void HandleClientComm( object client )
  {
    TcpClient tcpClient = (TcpClient)client;
    NetworkStream clientStream = tcpClient.GetStream();
    int maxReadLength = 4096;

    byte[] message = new byte[maxReadLength];
    int bytesRead;
    
    Console.WriteLine("Connected!");
    while( true )
    {
      bytesRead = 0;
      try
      {
        bytesRead = clientStream.Read(message, 0, maxReadLength);
        Console.WriteLine("Read");
      }
      catch
      {
        break;
      }

      if (bytesRead == 0)
      {
        break;
      }
      ASCIIEncoding enc = new ASCIIEncoding();

      //Print out the request
      Console.WriteLine( ":" + enc.GetString( message, 0, bytesRead ) + ":");

      //Sned back a response.
      String output = "<!DOCTYPE HTML><html><body><h1>404 Not Found</h1>The requested URL was not found on this server.<hr/><i>server</i></body></html>";
      String outputMIMEType = "text/html; charset=utf-8";
      String HTTPHeader = 
//        "HTTP/1.1 200 OK\nContent-Type: " + outputMIMEType + "\nContent-Length: " + output.Length + "\n\n";
        "HTTP/1.1 404 Not Found\nContent-Type: " + outputMIMEType + "\nContent-Length: " + output.Length + "\n\n";
      byte[] buffer = enc.GetBytes(
        HTTPHeader
        + output        
      );
      clientStream.Write(buffer, 0, buffer.Length);
      clientStream.Flush();
    }
    
    Console.WriteLine("Disconnecting.");
    tcpClient.Close();
  }
}

public class UDPListen
{
  public static void Main()
  {
    new RMWebServer(81);
    Console.ReadLine();
  }
}

