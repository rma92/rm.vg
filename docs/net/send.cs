using System;
using System.Net;
using System.Net.Sockets;
using System.Text;

class Hello 
{
	static void Main() 
	{
	Boolean done = false;
	Boolean error = false;
	Socket sending_socket = new Socket(AddressFamily.InterNetwork, SocketType.Dgram, ProtocolType.Udp);
	IPAddress toAddress = IPAddress.Parse("129.171.49.255");
//	IPAddress toAddress = IPAddress.Parse("129.171.255.255");
	IPEndPoint endPoint = new IPEndPoint(toAddress,5309);

	Console.WriteLine("enter a message. Enter a blank line to exit.");
	while(!done)
	{
		string rIn = Console.ReadLine();
		if(rIn.Length == 0)
		{
			done = true;
		}
		else
		{
			byte[] sendBuffer = Encoding.ASCII.GetBytes(rIn);
			Console.WriteLine("sending to address: {0} port: {1}", endPoint.Address, endPoint.Port );
			try
			{
				sending_socket.SendTo(sendBuffer, endPoint);
			}
			catch (Exception e)
			{
				error = true;
				Console.WriteLine(" Exception {0}", e.Message);
			}
			if(!error) Console.WriteLine("sent!");
			else Console.WriteLine("not sent!");
		}
	}
	Console.WriteLine("Press any key to exit.");
	Console.ReadKey();
	}//end of main()
}
