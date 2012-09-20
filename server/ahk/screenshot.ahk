;source http://www.autohotkey.com/community/viewtopic.php?t=49574

 SetBatchLines -1
  #NoEnv
  #SingleInstance force
 
  OnExit, DoExit

  ; gdi startup
  hModule := DllCall("LoadLibrary", "str", "gdiplus.dll")
  VarSetCapacity(si, 16, 0), si := Chr(1)
  DllCall("gdiplus\GdiplusStartup", "UintP", pToken, "Uint", &si, "Uint", 0)
   
  ; get jpg codec
  sExtTo = jpg
  DllCall("gdiplus\GdipGetImageEncodersSize", "UintP", nCount, "UintP", nSize)
  VarSetCapacity(ci, nSize)
  DllCall("gdiplus\GdipGetImageEncoders", "Uint", nCount, "Uint", nSize, "Uint", &ci)
  Loop, %nCount%
  {
     pTypes := DecodeInteger(&ci + 76 * (A_Index - 1) + 44)
     Unicode2Ansi(pTypes, sTypes)
     If InStr(sTypes, "." . sExtTo)
     {
        pCodec := &ci + 76 * (A_Index - 1)
        Break
     }
  }
  If !pCodec
     ExitApp
 
  WinGet, hw_frame, id, "Program Manager"   ; Desktop

  IfNotExist %A_ScriptDir%\screenshots
    FileCreateDir %A_ScriptDir%\screenshots

	GoSub, SaveScreenShot ; ijor was created this line
Return ; end of auto-execute section


; ^!p:: ; control + alt + p ;ijor was disabled this line
  ; GoSub, SaveScreenShot ;ijor was disabled this line
; Return ;ijor was disabled this line

SaveScreenShot: 
    ; variables
  ; sFileTo = %A_ScriptDir%\screenshots\%A_Now%.jpg ;ijor was disabled this line
  sFileTo = %A_ScriptDir%\screenshots\tmp.jpg ;ijor was created this line
  Ansi2Unicode(sFileTo, wFileTo)
 
  ; get screen
  hdc_frame := DllCall("GetDC", "Uint", hw_frame)
  hdc_buffer := DllCall("gdi32.dll\CreateCompatibleDC", "Uint", hdc_frame)
  hbm_buffer := DllCall("gdi32.dll\CreateCompatibleBitmap"
                        , "Uint", hdc_frame
                        , "int", A_ScreenWidth
                        , "int", A_ScreenHeight)
  DllCall("gdi32.dll\SelectObject"
          , "Uint", hdc_buffer, "Uint", hbm_buffer)
 
  ; Copy BMP from DC
  DllCall("gdi32.dll\BitBlt"
          , "Uint", hdc_buffer, "int", 0, "int", 0
          , "int", A_ScreenWidth, "int", A_ScreenHeight
          , "Uint", hdc_frame,  "int", 0, "int", 0, "Uint", 0x00CC0020)
  DllCall("gdiplus\GdipCreateBitmapFromHBITMAP"
          , "Uint", hbm_buffer, "Uint", 0, "UintP", pImage)
 
  ; ??? Build jpg encoding from bmp?
 
  ; create jpg encoding
  DllCall("gdiplus\GdipGetEncoderParameterListSize"
          , "Uint", pImage, "Uint", pCodec, "UintP", nSize)
  VarSetCapacity(pi, nSize)
  DllCall("gdiplus\GdipGetEncoderParameterList"
          , "Uint", pImage, "Uint", pCodec, "Uint", nSize, "Uint", &pi)
 
  nValue := 30                        ; JPEG Quality: 0 - 100
  EncodeInteger(&pi + 28, 1)                  ; number of list
  ;32 - 48                        ; 16 byte CLSID of Encoder
  EncodeInteger(&pi + 48, 1)                  ; number of values
  EncodeInteger(&pi + 52, 4)                  ; type of values
  EncodeInteger(DecodeInteger(&pi + 56), nValue)            ; address of the value
  pParam := &pi + 28
 
  ; save image
  DllCall("gdiplus\GdipSaveImageToFile", "Uint", pImage, "Uint", &wFileTo, "Uint", pCodec, "Uint", pParam)

  DllCall("gdiplus\GdipDisposeImage", "Uint", pImage)

Return

; from COM lib
Ansi2Unicode(ByRef sString, ByRef wString, nSize = "")
{
   If (nSize = "")
       nSize:=DllCall("kernel32\MultiByteToWideChar", "Uint", 0, "Uint", 0, "Uint", &sString, "int", -1, "Uint", 0, "int", 0)
   VarSetCapacity(wString, nSize * 2 + 1)
   DllCall("kernel32\MultiByteToWideChar", "Uint", 0, "Uint", 0, "Uint", &sString, "int", -1, "Uint", &wString, "int", nSize + 1)
   Return   &wString
}

Unicode2Ansi(ByRef wString, ByRef sString, nSize = "")
{
   pString := wString + 0 > 65535 ? wString : &wString
   If (nSize = "")
       nSize:=DllCall("kernel32\WideCharToMultiByte", "Uint", 0, "Uint", 0, "Uint", pString, "int", -1, "Uint", 0, "int",  0, "Uint", 0, "Uint", 0)
   VarSetCapacity(sString, nSize)
   DllCall("kernel32\WideCharToMultiByte", "Uint", 0, "Uint", 0, "Uint", pString, "int", -1, "str", sString, "int", nSize + 1, "Uint", 0, "Uint", 0)
   Return   &sString
}

DecodeInteger(ptr)
{
   Return *ptr | *++ptr << 8 | *++ptr << 16 | *++ptr << 24
}

EncodeInteger(ref, val)
{
   DllCall("ntdll\RtlFillMemoryUlong", "Uint", ref, "Uint", 4, "Uint", val)
}

; Exit
DoExit:

  ; gdi shutdown
  DllCall("gdi32.dll\DeleteObject", "Uint", hbm_buffer)
  DllCall("gdi32.dll\DeleteDC", "Uint", hdc_frame)
  DllCall("gdi32.dll\DeleteDC", "Uint", hdc_buffer)
 
  DllCall("gdiplus\GdiplusShutdown" , "Uint", pToken)
  DllCall("FreeLibrary", "Uint", hModule)

  ExitApp
Return