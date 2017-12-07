#define _WIN32_WINNT 0x0500
#define WINVER 0x0500
#include <windows.h>
//#include <stdio.h>

LPCSTR AppName = "TestApp";

int tick = 20;    //ms for each timer tick
int level = 0xFF; //0xFF is fully opaque, 0 is invisible. Initial Opacity.
int dec = 0x02;   //How much to decrement
int decdec = 0x1; //How much to increase the decrement. Set to 0 for constant rate.
int minlevel = 1;//If the opacity is below this level, the program quits.
VOID CALLBACK TimerProc(HWND hWnd, UINT uMsg, UINT_PTR idEvent, DWORD dwTime)
{
  if(dec += decdec, level -= dec, level > 0 && level > minlevel) //If we don't check this, we may accidentally make the window opaque again.
  {
    SetLayeredWindowAttributes(hWnd, RGB(255, 255, 254), level, LWA_COLORKEY | LWA_ALPHA);
  }
  else
  {
    PostQuitMessage(0);
  }
}
LRESULT CALLBACK WndProc(HWND hWnd, UINT message, WPARAM wParam, LPARAM lParam){
  switch(message){
    case WM_CREATE:
    {
      long x, y, width, height;
      HMONITOR hMonitor;
      MONITORINFO monitorinfo;
      hMonitor = MonitorFromWindow(hWnd, MONITOR_DEFAULTTOPRIMARY);
      monitorinfo.cbSize = sizeof(MONITORINFOEX);
      GetMonitorInfo( hMonitor, &monitorinfo );
      x = monitorinfo.rcMonitor.left;
      y = monitorinfo.rcMonitor.top;
      width = monitorinfo.rcMonitor.right - monitorinfo.rcMonitor.left;
      height = monitorinfo.rcMonitor.bottom - monitorinfo.rcMonitor.top;
      SetWindowPos(hWnd, HWND_TOPMOST, x, y, width, height, 0);
      SetLayeredWindowAttributes(hWnd, RGB(255, 255, 254), level, LWA_COLORKEY | LWA_ALPHA);
      SetTimer(hWnd, 1, tick, TimerProc);
      break;
    }
    case WM_DESTROY:
      PostQuitMessage(0);
      break;
    default:
      return DefWindowProc(hWnd, message, wParam, lParam);
  }
  return 0;
}
int WINAPI WinMain(HINSTANCE hInst, HINSTANCE hPrevInst, LPSTR lpCmdLine, int nCmdShow){
  HMONITOR d1;
  MONITORINFO disp;
  MSG msg1;
  long x, y, w, h;
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
  hWnd1 = CreateWindow(AppName, AppName, WS_POPUP , 10, 10, 360, 240, 0, 0, hInst, 0);
  if(hWnd1 == NULL) return 0;
  ShowWindow(hWnd1, SW_HIDE); 
  SetWindowLong (hWnd1 , GWL_EXSTYLE , GetWindowLong (hWnd1 , GWL_EXSTYLE ) | WS_EX_TOOLWINDOW | WS_EX_LAYERED | WS_EX_TRANSPARENT ) ;
  ShowWindow(hWnd1, SW_SHOW); 
  while(GetMessage(&msg1,NULL,0,0) > 0)
  {
    TranslateMessage(&msg1);
    DispatchMessage(&msg1);
  }
  return msg1.wParam;
}

