#define WIN32_LEAN_AND_MEAN
#include <stdio.h>
#include <time.h>
#include <malloc.h>
#include <winsock2.h>
#include <ws2tcpip.h>
#include <windows.h>

//GCC Win32: gcc mcsend.c -l ws2_32
//MS VCC:    cl mcsend.c /link WS2_32.LIB
int send_msg(char* buf, int sz_buf, struct in_addr to_addr, int port, struct in_addr local_ip, int local_port)
{
  int err;
  struct sockaddr_in service_in;
  struct sockaddr_in recv_addr;  
  SOCKET sock;
  
  //create a socket.
  sock = WSASocket(AF_INET, SOCK_DGRAM, IPPROTO_UDP, 0, 0, 0);
  if(sock == SOCKET_ERROR)
  {
    printf("failed to get socket:%d\n", WSAGetLastError()); return 0;
  }
  //bind to specify local port
  service_in.sin_family = AF_INET;
  service_in.sin_addr = local_ip;
  service_in.sin_port = htons(local_port); 
  err = bind(sock, (SOCKADDR*) &service_in, sizeof(service_in));
  if(err == SOCKET_ERROR)
  {
    printf("Error with bind(): %d\n",
      WSAGetLastError());
    return 0;
  }
  else
  {
    printf("bind okay on %s:%d\n",
      inet_ntoa(service_in.sin_addr),
      local_port);
  }
  //set the output IP.
  recv_addr.sin_family = AF_INET;
  recv_addr.sin_addr = to_addr;
  recv_addr.sin_port = htons(port);
  
  sendto(sock, buf, sz_buf, 0, (SOCKADDR *) & recv_addr, sizeof(recv_addr));
  return 1;
}

int main(int argc, char ** argv)
{
  SOCKET sock;
  WSADATA WinsockData;
  int port = 5555;
  int err;
  char* send_buf;
  char* mc_domain_ip;
  struct in_addr mc_ip_addr;
  struct in_addr local_ip_addr;  
  //handle parameters
  if(argc < 4)
  {
    printf("usage: mcsend mc_domain port message\nex: mcsend 239.1.2.3 5555 hi\nSend hi to everyone on 239.1.2.3 port 5555");
    return 0;
  }
  mc_domain_ip = argv[1];
  port = atoi(argv[2]);
  send_buf = argv[3];

  //startup WinSock
  if(WSAStartup(MAKEWORD(2, 2), &WinsockData) != 0)
  {
    printf("wrong winsock version.");
    return 2;
  }

  mc_ip_addr.S_un.S_addr = inet_addr(mc_domain_ip);
  local_ip_addr.S_un.S_addr = 0;

  //call send_msg function above.
  send_msg(send_buf, strlen(send_buf), mc_ip_addr, port, local_ip_addr, 0);

}

