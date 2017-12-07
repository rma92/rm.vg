#include <windows.h>
LPCSTR AppName = "ToHex";
char * convBuf;
char * inBuf;
int i;
HANDLE hButton1, hEdit1, hEdit2;
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
          SendMessage(hEdit1, WM_GETTEXT, (WPARAM)100, inBuf);
          for(i = 0; i < strlen(inBuf);++i)
          {
            sprintf(convBuf + i * 3, "%x ", inBuf[i]);
          }
          //sprintf(convBuf, "Hello, %s!", inBuf);
          SendMessage(hEdit2, WM_SETTEXT, 0, convBuf);
        }
          break;
        default:
          break;
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
  wc1.hbrBackground = GetSysColorBrush(COLOR_BTNFACE);
  wc1.hIcon = LoadIcon(NULL, IDI_INFORMATION);
  wc1.hCursor = LoadCursor(NULL, IDC_ARROW);
  convBuf = (char*)calloc(1, 300);
  inBuf = (char*)calloc(1, 300);
  if(RegisterClass(&wc1) == FALSE) return 0;
  hWnd1 = CreateWindow(AppName, AppName, WS_OVERLAPPEDWINDOW | WS_VISIBLE, 10, 10, 208, 158, 0, 0, hInst, 0);
  hButton1 = CreateWindow("BUTTON", "Go", WS_TABSTOP | WS_VISIBLE | WS_CHILD | BS_DEFPUSHBUTTON, 140, 50, 40, 24, hWnd1, 322, hInst, 0);
  CreateWindow("STATIC", "Enter ASCII to convert to hex.", WS_TABSTOP | WS_VISIBLE | WS_CHILD | ES_LEFT, 10, 10, 160, 34, hWnd1, 322, hInst, 0);
  hEdit1 = CreateWindow("EDIT", "", WS_TABSTOP | WS_VISIBLE | WS_CHILD | ES_LEFT | WS_BORDER, 10, 50, 120, 23, hWnd1, 0, hInst, 0);
  hEdit2 = CreateWindow("EDIT", "", WS_TABSTOP | WS_VISIBLE | WS_CHILD | ES_LEFT | WS_BORDER, 10, 80, 170, 23, hWnd1, 0, hInst, 0);
  if(hWnd1 == NULL) return 0;
  while(GetMessage(&msg1,NULL,0,0) > 0){
    TranslateMessage(&msg1);
    DispatchMessage(&msg1);
  }
  return msg1.wParam;
}

