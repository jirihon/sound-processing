# Sound processing

Workflow for sound editing in Audacity and export to mp3 using ffmpeg.

## Directory structure

- `raw/` - raw recordings in WAV format
- `flac/` - songs cut from raw recordings with fade in and fade out in FLAC format
- `master/` - songs with custom effects applied (soft limiter, etc.) in FLAC format
- `mp3/` - songs exported to MP3 format using the export.php script

## Audacity macros

### Fade In

1. **Select** Start="0" End="0.2" Mode="Set" RelativeTo="ProjectStart"
2. **AdjustableFade** curve="0" gain0="0" gain1="100" preset="ExponentialIn" type="Up" units="Percent"
3. END

## Fade Out

1. **AdjustableFade** curve="0" gain0="100" gain1="0" preset="None" type="SCurveDown" units="Percent"
2. **Select** Start="0" End="-4" Mode="Set" RelativeTo="SelectionEnd"
3. **Silence** Use_preset="<Current Settings>"
4. **CursorRight**
5. **SelCursorToTrackEnd**
6. **Cut**
7. END

## Custom Audacity shortcuts

- Ctrl+Shift+S Fade In
- Ctrl+Shitf+D Fade Out
- Ctrl+Shitf+Q Normalize
- Ctrl+Shift+W Limitter (Soft limitter)
