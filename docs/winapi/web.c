#include <windows.h>
#include <wininet.h>

/*
  WinInet test browser
  A small program that uses WinInet to download a page.
  TCC compiling: 
    use tiny_impdef on wininet.dll.  
    Put wininet.def in the lib folder for tcc. 
    tcc32 -lwininet web.c
  GCC compiling:
    gcc web.c -lwininet
  Microsoft compiling: 
    cl web.c wininet.lib user32.lib
*/
LPCSTR AppName = "TestApp";
HANDLE hButton1, hEdit1, hEdit2;
static int urlBufferLength = 8192;

void do_web_load(char * domain, char * path)
{
  static CHAR hdrs[] =
      "Content-Type: text/html\nMySpecialHeder: whatever";
  static CHAR frmdata[] =
      "";
  static LPSTR accept[2]={"*/*", NULL};

  CHAR szBuffer[1025];
  DWORD dwRead=0;
  int ndx = 0;

  HINTERNET hSession = InternetOpen("MyAgent",
    INTERNET_OPEN_TYPE_PRECONFIG, NULL, NULL, 0 );
  HINTERNET hConnect = InternetConnect( hSession, domain,
    INTERNET_DEFAULT_HTTP_PORT, NULL, NULL, INTERNET_SERVICE_HTTP, 0, 1);
  HINTERNET hRequest = HttpOpenRequest(hConnect, "GET", path,
    NULL, NULL, accept, 0, 1);
  if( !HttpSendRequest( hRequest, hdrs, strlen(hdrs), frmdata, strlen(frmdata) ) )
  {
    MessageBox(0, "HTTP Error.", "Error", 0);
    //printf("Error %d", GetLastError());
  }
  
  SetWindowText( hEdit2, "");
  while( InternetReadFile( hRequest, szBuffer, sizeof( szBuffer) -1, &dwRead)
        && dwRead )
  {
    szBuffer[dwRead] = 0;
    //MessageBoxA(0, szBuffer, "file", 0);
    dwRead = 0;
    ndx = GetWindowTextLength(hEdit2);
    SendMessage( hEdit2, EM_SETSEL, (WPARAM)ndx, (LPARAM)ndx );
    SendMessage( hEdit2, EM_REPLACESEL, 0, (LPARAM) ((LPSTR) szBuffer));
  }
  InternetCloseHandle( hRequest );
  InternetCloseHandle( hConnect );
  InternetCloseHandle( hSession );
}

void split_url()
{
  int file_path_index = urlBufferLength;
  int colon = 0;
  int start_of_domain = 0;
  int end_of_domain = 0;
  int slash_count = 0;

  int i = 0;

  char * buffer = (char*)malloc( urlBufferLength );
  char * domain = (char*) malloc( urlBufferLength );

  GetWindowText( hEdit1, buffer, urlBufferLength - 1);
  //Check to see if the first characters are ASCII "http":
  if( (((int*)buffer)[0] | 0x20202020) == 0x70747468 )
  {
    char* buffer2 = buffer;
    char* domain2 = domain;
    while(*buffer2 != '\0') 
    {
      if( start_of_domain && !end_of_domain )
      {
        *domain2 = *buffer2;
        domain2++;
      }

      if( colon && *buffer2 == '/' )
      {
        ++slash_count;
        if(slash_count == 2 )
        {
          start_of_domain = buffer2 + 1;
        }
        if( slash_count == 3 )
        {
          end_of_domain = buffer2;
          *(domain2-1) = '\0';

          break;
        }
      }
      if( *buffer2 == ':' )
      {
        ++colon;
      }

      buffer2++;
    }

    do_web_load( domain, buffer2 );
  }
  else
  {
    MessageBox(0, "invalid protocol", 0, 0);
  }

  free( buffer );
  free( domain );
}

LRESULT CALLBACK WndProc(HWND hWnd, UINT message, WPARAM wParam, LPARAM lParam)
{
  switch(message)
  {
    case WM_CREATE:
      break;
    case WM_DESTROY:
      PostQuitMessage(0);
      break;
    case WM_COMMAND:
      switch(LOWORD(wParam))
      {
        case 322:
        {
          split_url();
        }
          break;
        default:
          break;
      }
      break;
    case WM_SIZE:
      {
        INT nWidth = LOWORD(lParam);
        INT nHeight = HIWORD( lParam);
        MoveWindow( hButton1, nWidth - 40, 0, 40, 24, TRUE );
        MoveWindow( hEdit1, 40, 0, nWidth - 90, 24, TRUE );
        MoveWindow( hEdit2, 0, 40, nWidth, nHeight - 40, TRUE);
      }
    default:
      return DefWindowProc(hWnd, message, wParam, lParam);
  }
  return 0;
}
int WINAPI WinMain(HINSTANCE hInst, HINSTANCE hPrevInst, LPSTR lpCmdLine, int nCmdShow)
{
  MSG msg1;
  WNDCLASS wc1;
  HWND hWnd1;
  ZeroMemory(&wc1, sizeof wc1);
  wc1.hInstance = hInst;
  wc1.lpszClassName = AppName;
  wc1.lpfnWndProc = (WNDPROC)WndProc;
  wc1.style = CS_DBLCLKS | CS_VREDRAW | CS_HREDRAW;
  wc1.hbrBackground = GetSysColorBrush(COLOR_WINDOW);
  wc1.hIcon = LoadIcon(NULL, IDI_INFORMATION);
  wc1.hCursor = LoadCursor(NULL, IDC_ARROW);
  if(RegisterClass(&wc1) == FALSE) return 0;
  hWnd1 = CreateWindow(AppName, AppName, WS_OVERLAPPEDWINDOW | WS_VISIBLE, 10, 10, 360, 240, 0, 0, hInst, 0);
  if(hWnd1 == NULL) return 0;
hButton1 = CreateWindow("BUTTON", "Go", WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON , 304, 0, 40, 24, hWnd1, (HANDLE)322, hInst, 0);
  CreateWindow("STATIC", "URL:", WS_TABSTOP | WS_VISIBLE | WS_CHILD | ES_LEFT, 0, 0, 160, 24, hWnd1, (HANDLE)322, hInst, 0);
  hEdit1 = CreateWindow("EDIT", "http://rm.vg/", WS_TABSTOP | WS_VISIBLE | WS_CHILD | ES_LEFT | WS_BORDER, 40, 0, 250, 24, hWnd1, 0, hInst, 0);
  hEdit2 = CreateWindow("EDIT", "", WS_TABSTOP | WS_VISIBLE | WS_CHILD | WS_VSCROLL | ES_LEFT | ES_MULTILINE | ES_AUTOVSCROLL | WS_BORDER, 0, 40, 360, 200, hWnd1, 0, hInst, 0);  
  while(GetMessage(&msg1,NULL,0,0) > 0){
    TranslateMessage(&msg1);
    DispatchMessage(&msg1);
  }
  return msg1.wParam;
}

