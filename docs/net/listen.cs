using System;
using System.Net;
using System.Net.Sockets;
using System.Text;

public class UDPListen
{
	private const int listenPort = 5309;
	public static void Main()
	{
		bool done = false;
		UdpClient listener = new UdpClient(listenPort);
		IPEndPoint groupEP = new IPEndPoint(IPAddress.Any, listenPort);
		string receivedData;
		byte[] receivedByteArray;
		try
		{
			while(!done)
			{
				Console.WriteLine("waiting.");
				receivedByteArray = listener.Receive(ref groupEP);
				Console.WriteLine("Got from {0}", groupEP.ToString());
				receivedData = Encoding.ASCII.GetString(receivedByteArray, 0, receivedByteArray.Length);
				Console.WriteLine("data====\n{0}\n====end data", receivedData);
			} //end while
		}
		catch(Exception e)
		{
			Console.WriteLine(e.ToString());
		}
		listener.Close();
		return;
	}
}