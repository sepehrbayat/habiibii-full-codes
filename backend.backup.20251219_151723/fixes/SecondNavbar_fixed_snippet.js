  const handleOpenPopover = () => {
    // Ensure anchorRef.current is valid before opening popover
    // اطمینان از معتبر بودن anchorRef.current قبل از باز کردن popover
    if (anchorRef?.current) {
      setOpenPopover(true);
    } else {
      console.warn("AccountPopover: anchorRef.current is null, cannot open popover");
    }
  };

