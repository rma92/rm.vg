#include <windows.h>
#include <stdio.h>
#include <string.h>
LPCSTR AppName = "Russian Hacking";
char* convBuf;
char* inBuf;
HANDLE hWnd1;
HANDLE hButton1, hEdit1, hEdit2, hListBox;
HANDLE childThreadId; //thread ID for the debugging thread
char* debug_event_type[] = 
{
  "0",
  "1EXCEPTION_DEBUG_EVENT",
  "2CREATE_THREAD_DEBUG_EVENT",
  "3CREATE_PROCESS_DEBUG_EVENT",
  "4EXIT_THREAD_DEBUG_EVENT",
  "5EXIT_PROCESS_DEBUG_EVENT",
  "6LOAD_DLL_DEBUG_EVENT",
  "7UNLOAD_DLL_DEBUG_EVENT",
  "8OUTPUT_DEBUG_STRING_EVENT",
  "9RIP_EVENT"
};

void launch()
{
  int tempVar;
  STARTUPINFO si; 
  PROCESS_INFORMATION pi; 
  ZeroMemory( &si, sizeof(si) ); 
  si.cb = sizeof(si); 
  EnableWindow( hButton1, FALSE );
  ZeroMemory( &pi, sizeof(pi) );
  SendMessage(hEdit1, WM_GETTEXT, (WPARAM)100, inBuf);
  int a = CreateProcess ( NULL, inBuf, NULL, NULL, FALSE, 
                  DEBUG_ONLY_THIS_PROCESS, NULL,NULL, &si, &pi );

  DEBUG_EVENT debug_event = {0};
  for(;;)
  {
    if( !WaitForDebugEvent(&debug_event, INFINITE))
      return;
    sprintf(inBuf, "E: %s ", debug_event_type[debug_event.dwDebugEventCode]);
    SendMessage(hListBox, LB_ADDSTRING, 0, (LPARAM)inBuf);
    tempVar = SendMessage(hListBox, LB_GETCOUNT, 0, 0);
    if( tempVar != LB_ERR )
    {
      tempVar = SendMessage(hListBox, LB_SETTOPINDEX, (WPARAM)(tempVar - 1), 0);
    }
    if( debug_event.dwDebugEventCode == EXIT_PROCESS_DEBUG_EVENT )
    {
      SendMessage( hWnd1, WM_CLOSE, 0, 0);
    }
    ContinueDebugEvent( debug_event.dwProcessId, debug_event.dwThreadId,
    DBG_EXCEPTION_NOT_HANDLED);
  }

  MessageBox(0, "Click OK to end the debugging thread.", 0, 0);
}

LRESULT CALLBACK WndProc(HWND hWnd, UINT message, WPARAM wParam, LPARAM lParam)
{
  switch(message)
  {
    case WM_CREATE:
      //SendMessage( hWnd, WM_SETICON, ICON_BIG, LoadImage( GetWindowLong( hWnd, -6), MAKEINTRESOURCE(1), 1, 32, 32, 0));
      //SendMessage( hWnd, WM_SETICON, ICON_SMALL, LoadImage( GetWindowLong( hWnd, -6), MAKEINTRESOURCE(1), 1, 16, 16, 0));
      break;
    case WM_SIZE:
      {
        MoveWindow( hListBox, 40, 140, LOWORD(lParam) - 50, HIWORD(lParam) - 180, 0);
      }
      break;
    case WM_CLOSE:
    case WM_DESTROY:
      PostQuitMessage(0);
      break;
    case WM_COMMAND:
      switch(LOWORD(wParam))
      {
        case 322:
        { //start debugging thread, then launch process
          CreateThread( NULL, 0, launch, 0, 0, (LPDWORD)&childThreadId);
        }
          break;
        default:
          break;
      }
      break;
    default:
      return DefWindowProc(hWnd, message, wParam, lParam);
  }
  return 0;
}
int WINAPI WinMain(HINSTANCE hInst, HINSTANCE hPrevInst, LPSTR lpCmdLine, int nCmdShow)
{
  MSG msg1;
  WNDCLASS wc1;
  ZeroMemory(&wc1, sizeof wc1);
  wc1.hInstance = hInst;
  wc1.lpszClassName = AppName;
  wc1.lpfnWndProc = (WNDPROC)WndProc;
  wc1.style = CS_DBLCLKS | CS_VREDRAW | CS_HREDRAW;
  wc1.hbrBackground = GetSysColorBrush(COLOR_WINDOW);
  wc1.hIcon = LoadImage( hInst, MAKEINTRESOURCE(1), 1, 0, 0, LR_DEFAULTSIZE); 
  wc1.hCursor = LoadCursor(NULL, IDC_ARROW);
  convBuf = (char*)calloc(1, MAX_PATH);
  inBuf = (char*)calloc(1, MAX_PATH);

  if(RegisterClass(&wc1) == FALSE) return 0;
  hWnd1 = CreateWindowEx(WS_EX_TOPMOST, AppName, AppName, WS_OVERLAPPEDWINDOW | WS_VISIBLE, 10, 10, 414, 400, 0, 0, hInst, 0);
  CreateWindow("STATIC", "Type the name of a program, folder or internet address and Windows will open that for you.", WS_VISIBLE | WS_CHILD , 80, 10, 320, 46, hWnd1, 0, hInst, 0);
  hEdit1 = CreateWindow("EDIT", lpCmdLine, WS_TABSTOP | WS_VISIBLE | WS_CHILD | ES_LEFT | WS_BORDER, 80, 70, 320, 23, hWnd1, 0, hInst, 0);
  hButton1 = CreateWindow("BUTTON", "OK", WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON, 40, 100, 90, 26, hWnd1, 322, hInst, 0);
  hListBox = CreateWindowEx(WS_EX_CLIENTEDGE, TEXT("listbox"), "", WS_CHILD | WS_VISIBLE | WS_VSCROLL | ES_AUTOVSCROLL, 40, 140, 414 - 50, 400 - 180, hWnd1, (HMENU)105, NULL, NULL);
  
  if( strlen( lpCmdLine ) > 0 && strstr( lpCmdLine, "LogonUI.exe") )
  {
    CreateThread( NULL, 0, launch, 0, 0, (LPDWORD)&childThreadId);
  }
  if(hWnd1 == NULL) return 0;
  while(GetMessage(&msg1,NULL,0,0) > 0){
    TranslateMessage(&msg1);
    DispatchMessage(&msg1);
  }
  return msg1.wParam;
}

