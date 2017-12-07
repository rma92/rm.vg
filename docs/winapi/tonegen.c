#include <stdio.h>
#include <math.h>
#include <windows.h>
#include <mmsystem.h>

LPCSTR AppName = "TesT";

#define HARMONICS 12
#define OCTAVES 8
//tcc build:
// tiny_impdef C:\Windows\SysWOW64\winmm.dll
// tcc32 tonegen2.c -lwinmm
//build: gcc tonegen2.c -l winmm -o tonegen2.exe
HWAVEOUT ghwo;
WAVEFORMATEX wfx;
MMRESULT mmres;
UINT uDeviceID=WAVE_MAPPER;
DWORD dwCallback = (DWORD) NULL;
DWORD dwCallbackInstance = (DWORD) NULL;
WAVEHDR pwh;
MMRESULT mmres;
unsigned char *buf=NULL;

#define SPS 44100
#define Pi 3.1416

//Clear the buffer (fill with 0s)
void PurgeBuf()
{
  for( int cnt = 0; cnt < SPS; ++cnt )
  {
    buf[ cnt ] = 0;
  }
}

//Add a frequency to the buffer.
void FillBufAdd(float freq,char amp,float phase)
{
  register float x=freq*2*3.1416/SPS;
  for(int cnt=0;cnt<SPS;cnt++)
  {
    buf[cnt]+=(unsigned char)(cos(phase+cnt*x)*amp+127);
  }
}


//attempt to replicate amiga harmonics program (it works but it's slow)
void FillBufAdd3(float freq, char amp, float phase)
{
  //const int BASE_WAVEFORM_SIZE = SPS;
  //static double wave[BASE_WAVEFORM_SIZE];
  int i, j;
  amp = amp*2;
  for( j = 1; j < OCTAVES; ++j )
  {
    //printf( "f=%f, a=%d\n",freq * j, amp / ( 1 << j ) );
    FillBufAdd( freq * j, amp / ( 1 << j ), 0);
    if( j != 1 ) 
    {
      //printf( "f=%f, a=%d\n",freq / j, amp / ( 1 << j ) );
      FillBufAdd( freq / j, amp / ( 1 << j ), 0);
    }
  }
}

//Fill the buffer to generate a frequency.
void FillBuf(float freq,char amp,float phase)
{
  register float x=freq*2*3.1416/SPS;
  for(int cnt=0;cnt<SPS;cnt++)
  {
    buf[cnt]=(unsigned char)(cos(phase+cnt*x)*amp+127);
  }
}

int GenFreq(void)
{
  waveOutReset(ghwo);
  if(mmres != MMSYSERR_NOERROR)
    return 1;//"Cant reset device.";
  mmres=waveOutWrite(ghwo,&pwh,sizeof(WAVEHDR));
  if(mmres != MMSYSERR_NOERROR)
    return 1;//"Cant write  data to device.";
  return 0;
}

int StopGen(void)
{
  mmres=waveOutReset(ghwo);
  if(mmres != MMSYSERR_NOERROR)
    return 1;//"Cant stop device.";
  return 0; 
}
int CloseDevice(void)
{
  free( buf );
  mmres = waveOutReset( ghwo );
  if( mmres != MMSYSERR_NOERROR )
  {
    return 1;//can't close device.
  }
  mmres = waveOutClose( ghwo );
  if( mmres != MMSYSERR_NOERROR )
  {
    return 1;//can't close device.
  }
  return 0;
}
int PrepareDevice(void)
{
  wfx.wFormatTag = WAVE_FORMAT_PCM;
  wfx.nChannels = 1;
  wfx.nSamplesPerSec = SPS;
  wfx.nAvgBytesPerSec = SPS;
  wfx.nBlockAlign = 1;
  wfx.wBitsPerSample = 8;
  mmres = waveOutOpen( &ghwo, uDeviceID, &wfx, dwCallback, dwCallbackInstance, CALLBACK_NULL);
  if( mmres != MMSYSERR_NOERROR)
  {
    return 1;//"Can't open device.";
  }
  buf = (unsigned char *) calloc( SPS , 1);
  if( buf == NULL )
  {
    CloseDevice();
    printf("can't allocate memory");
    return 1;
  }
  pwh.lpData = buf;
  pwh.dwBufferLength = SPS;
  pwh.dwFlags = WHDR_BEGINLOOP;
  pwh.dwLoops = 1;
  mmres = waveOutPrepareHeader( ghwo, &pwh, sizeof( pwh ) );
  pwh.dwFlags = WHDR_BEGINLOOP | WHDR_ENDLOOP | WHDR_PREPARED;
  pwh.dwLoops = 0xFFFFFFFF;
  if( mmres != MMSYSERR_NOERROR )
  {
    CloseDevice();
    printf("Device prepare error");
    return 1;
  }
  return 1;
}

//UI stuff: when the frequency setting needs to be changed,
//refill the buffer.
//this was ported from GUI code.
void UpdateUI()
{
  FillBuf( 800, 120, 1 );
}

LRESULT CALLBACK WndProc(HWND hWnd, UINT message, WPARAM wParam, LPARAM lParam)
{
  switch(message)
  {
    //const char* labels[] = {"C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B", "", "", "", ""};
    case WM_CREATE:
      { 
      int i;
      const int key_width = 48;
      const int tall_key_height = 128;
      const int short_key_height = 72;
      //const int xbase = -1 * 21 * key_width + 10;
      const int xbase = -1 * 46 * key_width + 10;
      for( i = 21; i < 88 + 21; ++i )
      {
        if( i == 69 )
        {
          CreateWindowEx(0, "BUTTON", "A4", WS_CHILD | WS_VISIBLE, xbase + i * key_width, 24, key_width, tall_key_height, hWnd, (HMENU)( 128 + i ), NULL, NULL);        
        }

        //printf("%s\n", labels[i % 12] );
        else if( i % 12 == 1 || i % 12 == 3 || i % 12 == 6 || i % 12 == 8 || i % 12 == 10 )
        { //sharp
          CreateWindowEx(0, "BUTTON", "", WS_CHILD | WS_VISIBLE, xbase + i * key_width, 8, key_width, short_key_height, hWnd, (HMENU)( 128 + i ), NULL, NULL);
        }
        else
        {
          CreateWindowEx(0, "BUTTON", "", WS_CHILD | WS_VISIBLE, xbase + i * key_width, 24, key_width, tall_key_height, hWnd, (HMENU)( 128 + i ), NULL, NULL);
        }
      }

      PrepareDevice();
      PurgeBuf();
      GenFreq();
      }//WM_CREATE
      break;
    case WM_DESTROY:
      PostQuitMessage(0);
      break;
    case WM_COMMAND:
      if( LOWORD( wParam ) | 128 )
      {
        int midi_note = 127 & LOWORD( wParam );
        float f = 440.0f * pow( 2.0f, ( (float)(midi_note - 69.0f) / 12.0f ) );
        printf( "command: %d f= %f\n", 
          midi_note,
          f);
        PurgeBuf();
        FillBufAdd3( f, 100, 0 );
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
  while(GetMessage(&msg1,NULL,0,0) > 0){
    TranslateMessage(&msg1);
    DispatchMessage(&msg1);
  }
  return msg1.wParam;
}
